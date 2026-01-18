<?php

namespace App\Controllers\Shell;

use PHPFrame\BaseShell;
use PHPFrame\Facades\Db;

class DatabaseShell extends BaseShell
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 显示表结构信息
     * 调用: php shell.php database/describe table_name
     */
    public function describeAction($args)
    {
        $tableName = $args[0] ?? null;

        if (empty($tableName)) {
            $this->output("请提供表名", 'error');
            return 1;
        }

        try {
            $this->output("表结构信息: " . $tableName);
            
            // 检查表是否存在
            if (!Db::tableExists($tableName)) {
                $this->output("表不存在: " . $tableName, 'error');
                return 1;
            }
            
            // 获取表结构信息
            $tableInfo = Db::getTableInfo($tableName);
            
            if (empty($tableInfo)) {
                $this->output("无法获取表结构信息", 'error');
                return 1;
            }
            
            // 显示表结构
            $this->output("字段名\t\t类型\t\t是否为空\t默认值\t\t注释");
            $this->output("-".str_repeat("-", 80));
            
            foreach ($tableInfo as $column) {
                $field = $column->Field ?? $column->field ?? $column->column_name ?? '';
                $type = $column->Type ?? $column->type ?? $column->data_type ?? '';
                $null = $column->Null ?? $column->null ?? $column->is_nullable ?? '';
                $default = $column->Default ?? $column->default ?? $column->column_default ?? '';
                $comment = $column->Comment ?? $column->comment ?? '';
                
                $this->output(sprintf("%-15s\t%-15s\t%-8s\t%-15s\t%s", 
                    $field, $type, $null, $default, $comment));
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->output("获取表结构信息失败: " . $e->getMessage(), 'error');
            return 1;
        }
    }
    
    /**
     * 显示所有表列表
     * 调用: php shell.php database/tables
     */
    public function tablesAction($args)
    {
        try {
            $this->output("数据库表列表:");
            
            // 获取所有表名
            $connection = Db::connection();
            $driverName = $connection->getDriverName();
            
            switch ($driverName) {
                case 'mysql':
                    $tables = $connection->select("SHOW TABLES");
                    $tableField = 'Tables_in_' . $connection->getDatabaseName();
                    break;
                case 'pgsql':
                    $tables = $connection->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
                    $tableField = 'table_name';
                    break;
                case 'sqlite':
                    $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table'");
                    $tableField = 'name';
                    break;
                default:
                    $this->output("不支持的数据库类型: " . $driverName, 'error');
                    return 1;
            }
            
            if (empty($tables)) {
                $this->output("数据库中没有表");
                return 0;
            }
            
            foreach ($tables as $table) {
                $this->output("  " . $table->$tableField);
            }
            
            $this->output("\n总计: " . count($tables) . " 个表", 'success');
            return 0;
            
        } catch (\Exception $e) {
            $this->output("获取表列表失败: " . $e->getMessage(), 'error');
            return 1;
        }
    }

    /**
     * 导出数据库结构到Markdown文件
     * 调用: php shell.php database/build-structure
     */
    public function structureAction($args)
    {
        try {
            $this->output("开始导出数据库结构...");
            
            // 获取数据库连接
            $connection = Db::connection();
            $driverName = $connection->getDriverName();
            $databaseName = $connection->getDatabaseName();
            
            $this->output("数据库类型: " . $driverName);
            $this->output("数据库名称: " . $databaseName);
            
            // 获取所有表
            $tables = $this->getAllTables($driverName, $connection);
            
            if (empty($tables)) {
                $this->output("数据库中没有表");
                return 0;
            }
            
            $this->output("发现表数量: " . count($tables));
            
            // 生成Markdown内容
            $markdownContent = $this->generateMarkdownStructure($tables, $driverName, $connection);
            
            // 写入文件
            $structureFile = ROOT_PATH . '/database/structure.md';
            file_put_contents($structureFile, $markdownContent);
            
            $this->output("数据库结构已导出到: " . $structureFile, 'success');
            $this->output("总计导出表数量: " . count($tables), 'success');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->output("导出数据库结构失败: " . $e->getMessage(), 'error');
            return 1;
        }
    }

    /**
     * 生成模型字段类
     * 调用: php shell.php database/build-model-fields
     */
    public function fieldsAction($args)
    {
        try {
            $this->output("开始生成模型字段类...");
            
            // 获取数据库连接
            $connection = Db::connection();
            $driverName = $connection->getDriverName();
            $databaseName = $connection->getDatabaseName();
            
            $this->output("数据库类型: " . $driverName);
            $this->output("数据库名称: " . $databaseName);
            
            // 获取所有表
            $tables = $this->getAllTables($driverName, $connection);
            
            if (empty($tables)) {
                $this->output("数据库中没有表");
                return 0;
            }
            
            $this->output("发现表数量: " . count($tables));
            
            // 确保Fields目录存在
            $fieldsDir = ROOT_PATH. '/app/Models/Fields';
            if (!is_dir($fieldsDir)) {
                mkdir($fieldsDir, 0755, true);
            }
            
            // 加载模板
            $templateFile = ROOT_PATH . '/database/field_template.php';
            if (!file_exists($templateFile)) {
                $this->output("模板文件不存在: " . $templateFile, 'error');
                return 1;
            }
            
            $template = include $templateFile;
            
            $generatedCount = 0;
            
            // 为每个表生成字段类
            foreach ($tables as $tableName) {
                $this->output("处理表: " . $tableName);
                
                // 获取表字段信息
                $columns = $this->getTableColumns($tableName, $driverName, $connection);
                
                if (empty($columns)) {
                    $this->output("  跳过 - 无法获取字段信息");
                    continue;
                }
                
                // 生成字段类
                $className = $this->convertToPascalCase($tableName);
                $fieldConstantsData = $this->generateFieldConstants($columns);
                
                // 替换模板变量
                $classContent = str_replace(
                    ['{{TABLE_NAME}}', '{{TABLE_NAME_ORIGINAL}}', '{{FIELDS}}', '{{FIELD_CONSTANTS}}', '{{TIMESTAMP}}'],
                    [$className, $tableName, $fieldConstantsData['constants'], $fieldConstantsData['field_constants'], date('Y-m-d H:i:s')],
                    $template
                );
                
                // 写入文件
                $classFile = $fieldsDir . '/' . $className . 'Field.php';
                file_put_contents($classFile, $classContent);
                
                $this->output("  生成字段类: " . $className . 'Field.php', 'success');
                $generatedCount++;
            }
            
            $this->output("模型字段类生成完成", 'success');
            $this->output("总计生成字段类数量: " . $generatedCount, 'success');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->output("生成模型字段类失败: " . $e->getMessage(), 'error');
            return 1;
        }
    }
    
    /**
     * 获取所有表名
     */
    private function getAllTables($driverName, $connection)
    {
        switch ($driverName) {
            case 'mysql':
                $tables = $connection->select("SHOW TABLES");
                $tableField = 'Tables_in_' . $connection->getDatabaseName();
                break;
            case 'pgsql':
                $tables = $connection->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
                $tableField = 'table_name';
                break;
            case 'sqlite':
                $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table'");
                $tableField = 'name';
                break;
            default:
                throw new \Exception("不支持的数据库类型: " . $driverName);
        }
        
        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = $table->$tableField;
        }
        
        return $tableNames;
    }
    
    /**
     * 生成Markdown格式的数据库结构
     */
    private function generateMarkdownStructure($tables, $driverName, $connection)
    {
        $content = "# 数据库结构文档\n\n";
        $content .= "**生成时间**: " . date('Y-m-d H:i:s') . "\n\n";
        $content .= "**数据库类型**: " . $driverName . "\n\n";
        $content .= "**数据库名称**: " . $connection->getDatabaseName() . "\n\n";
        
        foreach ($tables as $tableName) {
            $content .= $this->generateTableStructure($tableName, $driverName, $connection);
        }
        
        return $content;
    }
    
    /**
     * 生成单个表的结构
     */
    private function generateTableStructure($tableName, $driverName, $connection)
    {
        // 获取表注释
        $tableComment = $this->getTableComment($tableName, $driverName, $connection);
        
        $content = "## 表: `{$tableName}`";
        if (!empty($tableComment)) {
            $content .= " - {$tableComment}";
        }
        $content .= "\n\n";
        
        // 获取表结构信息
        $columns = $this->getTableColumns($tableName, $driverName, $connection);
        $indexes = $this->getTableIndexes($tableName, $driverName, $connection);
        
        // 表字段结构
        $content .= "### 字段结构\n\n";
        $content .= "| 字段名 | 类型 | 是否为空 | 默认值 | 注释 |\n";
        $content .= "|--------|------|----------|--------|------|\n";
        
        foreach ($columns as $column) {
            $field = $column['field'];
            $type = $column['type'];
            $null = $column['null'];
            $default = $column['default'];
            $comment = $column['comment'];
            
            $content .= "| {$field} | {$type} | {$null} | {$default} | {$comment} |\n";
        }
        
        $content .= "\n";
        
        // 表索引信息
        if (!empty($indexes)) {
            $content .= "### 索引信息\n\n";
            $content .= "| 索引名 | 索引类型 | 索引字段 | 是否唯一 |\n";
            $content .= "|--------|----------|----------|----------|\n";
            
            foreach ($indexes as $index) {
                $indexName = $index['name'];
                $indexType = $index['type'];
                $indexColumns = $index['columns'];
                $unique = $index['unique'];
                
                $content .= "| {$indexName} | {$indexType} | {$indexColumns} | {$unique} |\n";
            }
            
            $content .= "\n";
        }
        
        $content .= "\n";
        
        return $content;
    }
    
    /**
     * 获取表字段信息
     */
    private function getTableColumns($tableName, $driverName, $connection)
    {
        $columns = [];
        
        switch ($driverName) {
            case 'mysql':
                $result = $connection->select("SHOW FULL COLUMNS FROM `{$tableName}`");
                foreach ($result as $column) {
                    $columns[] = [
                        'field' => $column->Field,
                        'type' => $column->Type,
                        'null' => $column->Null === 'YES' ? '是' : '否',
                        'default' => $column->Default ?? 'NULL',
                        'comment' => $column->Comment ?? ''
                    ];
                }
                break;
            case 'pgsql':
                $result = $connection->select("SELECT column_name, data_type, is_nullable, column_default, col_description((table_schema||'.'||table_name)::regclass, ordinal_position) as comment FROM information_schema.columns WHERE table_name = '{$tableName}'");
                foreach ($result as $column) {
                    $columns[] = [
                        'field' => $column->column_name,
                        'type' => $column->data_type,
                        'null' => $column->is_nullable === 'YES' ? '是' : '否',
                        'default' => $column->column_default ?? 'NULL',
                        'comment' => $column->comment ?? ''
                    ];
                }
                break;
            case 'sqlite':
                $result = $connection->select("PRAGMA table_info('{$tableName}')");
                foreach ($result as $column) {
                    $columns[] = [
                        'field' => $column->name,
                        'type' => $column->type,
                        'null' => $column->notnull ? '否' : '是',
                        'default' => $column->dflt_value ?? 'NULL',
                        'comment' => ''
                    ];
                }
                break;
        }
        
        return $columns;
    }
    
    /**
     * 获取表索引信息
     */
    private function getTableIndexes($tableName, $driverName, $connection)
    {
        $indexes = [];
        
        switch ($driverName) {
            case 'mysql':
                $result = $connection->select("SHOW INDEX FROM `{$tableName}`");
                $indexData = [];
                foreach ($result as $index) {
                    $indexName = $index->Key_name;
                    if (!isset($indexData[$indexName])) {
                        $indexData[$indexName] = [
                            'name' => $indexName,
                            'type' => $index->Index_type,
                            'unique' => $index->Non_unique ? '否' : '是',
                            'columns' => []
                        ];
                    }
                    $indexData[$indexName]['columns'][] = $index->Column_name;
                }
                
                foreach ($indexData as $index) {
                    $indexes[] = [
                        'name' => $index['name'],
                        'type' => $index['type'],
                        'columns' => implode(', ', $index['columns']),
                        'unique' => $index['unique']
                    ];
                }
                break;
            case 'pgsql':
                $result = $connection->select("SELECT indexname, indexdef FROM pg_indexes WHERE tablename = '{$tableName}'");
                foreach ($result as $index) {
                    $indexes[] = [
                        'name' => $index->indexname,
                        'type' => 'BTREE', // PostgreSQL默认使用BTREE
                        'columns' => $this->extractColumnsFromIndexDef($index->indexdef),
                        'unique' => strpos($index->indexdef, 'UNIQUE') !== false ? '是' : '否'
                    ];
                }
                break;
            case 'sqlite':
                $result = $connection->select("PRAGMA index_list('{$tableName}')");
                foreach ($result as $index) {
                    $indexInfo = $connection->select("PRAGMA index_info('{$index->name}')");
                    $columns = [];
                    foreach ($indexInfo as $info) {
                        $columns[] = $info->name;
                    }
                    
                    $indexes[] = [
                        'name' => $index->name,
                        'type' => 'BTREE', // SQLite默认使用BTREE
                        'columns' => implode(', ', $columns),
                        'unique' => $index->unique ? '是' : '否'
                    ];
                }
                break;
        }
        
        return $indexes;
    }
    
    /**
     * 获取表注释
     */
    private function getTableComment($tableName, $driverName, $connection)
    {
        $comment = '';
        
        switch ($driverName) {
            case 'mysql':
                $result = $connection->select("SHOW TABLE STATUS LIKE '{$tableName}'");
                if (!empty($result)) {
                    $comment = $result[0]->Comment ?? '';
                }
                break;
            case 'pgsql':
                $result = $connection->select("SELECT obj_description(oid) as comment FROM pg_class WHERE relname = '{$tableName}'");
                if (!empty($result)) {
                    $comment = $result[0]->comment ?? '';
                }
                break;
            case 'sqlite':
                // SQLite不支持表注释
                $comment = '';
                break;
        }
        
        return $comment;
    }
    
    /**
     * 从PostgreSQL索引定义中提取字段名
     */
    private function extractColumnsFromIndexDef($indexDef)
    {
        // 简单的正则匹配来提取字段名
        if (preg_match('/\(([^)]+)\)/', $indexDef, $matches)) {
            return $matches[1];
        }
        return '';
    }
    
    /**
     * 将表名转换为PascalCase格式
     */
    private function convertToPascalCase($tableName)
    {
        // 将整个表名转换为PascalCase，保留前缀信息避免冲突
        $words = explode('_', $tableName);
        $pascalCase = '';
        
        foreach ($words as $word) {
            $pascalCase .= ucfirst($word);
        }
        
        return $pascalCase;
    }
    
    /**
     * 生成字段常量定义和数组
     */
    private function generateFieldConstants($columns)
    {
        $constants = '';
        $fieldConstantsArray = [];
        
        foreach ($columns as $column) {
            $fieldName = $column['field'];
            $constantName = strtoupper($fieldName);
            
            // 生成字段注释
            $comment = "字段: {$fieldName}";
            if (!empty($column['comment'])) {
                $comment .= " - {$column['comment']}";
            }
            $comment .= " (类型: {$column['type']})";
            
            $constants .= "    /**\n";
            $constants .= "     * {$comment}\n";
            $constants .= "     */\n";
            $constants .= "    const {$constantName} = '{$fieldName}';\n\n";
            
            // 为getAll()方法准备字段常量数组
            $fieldConstantsArray[] = "self::{$constantName}";
        }
        
        return [
            'constants' => $constants,
            'field_constants' => implode(",\n            ", $fieldConstantsArray)
        ];
    }
}