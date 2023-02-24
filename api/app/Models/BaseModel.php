<?php

namespace App\Models;

use App\Services\Common\PublicService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Model
{
    public static function getInstance()
    {
        static $models = [];
        $class = get_called_class();
        $keyName = $class;
        if (!isset($models[$keyName])) {
            $models[$keyName] = new $class();
        }
        return $models[$keyName];
    }

    public static function createTable($model)
    {
        $sourceTable = $model->getSourceTableName();
        if (empty($model->mch_id) || $sourceTable === $model->table) {
            return;
        }
        $sql = "create table if not exists `{$model->table}` like `{$sourceTable}`";
        DB::update($sql);
    }


    public function getTableName(): string
    {
        return $this->table;
    }

    public function getTableColumn(): array
    {
        $rs = DB::select('select column_name as column_name from information_schema.columns where table_name = "' . $this->table . '"');
        return array_column($rs, 'column_name');
    }


    public function getMaxId($field): string
    {
        $rs = $this->select('id')
            ->orderByDesc($field)
            ->limit(1)
            ->first();
        if (empty($rs)) {
            return 0;
        } else {
            $rs = $rs->toArray();
            return $rs['id'];
        }
    }

    public function getSourceTableName(): string
    {
        return str_replace('_' . $this->mch_id, '', $this->table);
    }

    public function copyTable($where = '')
    {
        $sql = "REPLACE INTO `{$this->table}` SELECT * FROM  `{$this->getSourceTableName()}` ";
        if ($where) {
            $sql .= 'where ' . $where;
        }
        \Log::info('复制表=>' . $sql);
        DB::update($sql);
    }

    public function updateById($id, $data)
    {
        if (empty($id) || empty($data)) {
            return false;
        }
        return $this->where('id', $id)->update($data);
    }

    public function listByCond($attributes, $fields = ['*'])
    {
        if (empty($attributes)) {
            return false;
        }
        return $this->buildWhere($this->select($fields), $attributes)->get()->toArray();
    }

    public function delByCond($attributes)
    {
        if (empty($attributes)) {
            return false;
        }
        return $this->buildWhere($this, $attributes)->delete();
    }

    public function getByCond($attributes, $fields = ['*'])
    {
        if (empty($attributes)) {
            return false;
        }
        $rs = $this->buildWhere($this->select($fields), $attributes)->first();
        if (!empty($rs)) {
            $rs = $rs->toArray();
        } else {
            $rs = [];
        }
        return $rs;
    }

    public function getById($id, $fields = ['*'])
    {
        return $this->getByCond(['id' => $id], $fields);
    }

    public function updateByCond($attributes, $data)
    {
        if (empty($attributes)) {
            return false;
        }
        return $this->buildWhere($this, $attributes)->update($data);
    }

    public function buildWhere($query, $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $query = $query->where($key, $value);
            } elseif (is_array($value)) {
                switch (strtolower($value[0])) {
                    case 'in':
                        $query = $query->whereIn($key, $value[1]);
                        break;
                    case 'or':
                        $query = $query->orWhere($key, $value[1]);
                        break;
                    case 'between':
                        $query = $query->whereBetween($key, $value[1]);
                        break;
                    default:
                        $query = $query->where($key, $value[0], $value[1]);
                        break;
                }
            }
        }
        return $query;
    }

    public function listById($idArr, $fields = ['*'])
    {
        if (empty($idArr)) {
            return [];
        }
        return $this->listByCond(['id' => ['in', $idArr]], $fields);
    }

    public function listAll($fields = ['*'], $order = 'id', $dir = 'desc'): array
    {
        return $this
            ->select($fields)
            ->orderBy($order, $dir)
            ->get()
            ->toArray();
    }
}
