<?php

namespace Core\Model;

use Core\Database\Db;

class Model
{
    protected string $table;

    private array $propertyTypes = [];

    public function __construct()
    {
        $this->mapColumnsToProperties();
    }

    public static function create(array $data): self
    {
        $instance = new static();
        $instance->fill($data);
        return $instance;
    }

    public function fill($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function __set($name, $value)
    {
        if (isset($this->propertyTypes[$name])) {
            $type = $this->propertyTypes[$name];
            if ($this->validateType($value, $type)) {
                $this->$name = $value;
            } else {
                throw new \InvalidArgumentException("Invalid type for property $name. Expected $type.");
            }
        } else {
            $this->$name = $value;
        }
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
        throw new \InvalidArgumentException("Undefined property: $name");
    }

    private function mapColumnsToProperties()
    {
        $columns = $this->getTableColumns();
        foreach ($columns as $column) {
            $propertyName = $column['Field'];
            $propertyType = $this->mapMySQLTypeToPHP($column['Type']);
            $this->propertyTypes[$propertyName] = $propertyType;
            $this->{$propertyName} = $this->getDefaultValueForType($propertyType);
        }
    }

    private function getTableColumns()
    {
        $query = "DESCRIBE " . $_ENV['DB_PREFIX'] . $this->table;
        return Db::getInstance()->run($query)->fetchAll();
    }

    private function mapMySQLTypeToPHP(string $mysqlType): string
    {
        if (strpos($mysqlType, 'int') !== false) {
            return 'int';
        } elseif (strpos($mysqlType, 'float') !== false || strpos($mysqlType, 'double') !== false || strpos($mysqlType, 'decimal') !== false) {
            return 'float';
        } elseif (strpos($mysqlType, 'char') !== false || strpos($mysqlType, 'text') !== false) {
            return 'string';
        } elseif (strpos($mysqlType, 'date') !== false || strpos($mysqlType, 'time') !== false) {
            return 'string'; // or you could use DateTime
        } elseif (strpos($mysqlType, 'blob') !== false) {
            return 'string';
        } else {
            return 'mixed';
        }
    }

    private function getDefaultValueForType(string $type)
    {
        switch ($type) {
            case 'int':
                return 0;
            case 'float':
                return 0.0;
            case 'string':
                return '';
            case 'bool':
                return false;
            default:
                return null;
        }
    }

    private function validateType($value, string $type): bool
    {
        switch ($type) {
            case 'int':
                return is_int($value);
            case 'float':
                return is_float($value);
            case 'string':
                return is_string($value);
            case 'bool':
                return is_bool($value);
            default:
                return true; // For 'mixed' type
        }
    }
}