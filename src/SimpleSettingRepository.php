<?php

namespace Nece\Gears\SimpleSetting\Repository\ThinkPHP;

use Nece\Gears\Paginator;
use Nece\Gears\RepositoryAbstract;
use Nece\Gears\SimpleSetting\Entity\SimpleSettingEntity;
use Nece\Gears\SimpleSetting\Repository\ISimpleSettingRepository;
use Nece\Gears\SimpleSetting\Repository\ThinkPHP\Model\SimpleSetting;
use think\facade\Cache;

class SimpleSettingRepository extends RepositoryAbstract implements ISimpleSettingRepository
{
    /**
     * 删除
     *
     * @Author nece001@163.com
     * @DateTime 2023-08-27
     *
     * @param array $ids
     *
     * @return integer
     */
    public function delete(array $ids): int
    {
        return SimpleSetting::whereIn('id', $ids)->delete();
    }

    /**
     * 检查键名是否存在
     *
     * @Author nece001@163.com
     * @DateTime 2023-08-27
     *
     * @param SimpleSettingEntity $entity
     *
     * @return boolean
     */
    public function exists(SimpleSettingEntity $entity): bool
    {
        $query = SimpleSetting::where('key_name', $entity->key_name);
        if ($entity->id) {
            $query->where('id', '<>', $entity->id);
        }

        return $query->count() > 0;
    }

    /**
     * 创建或更新（只更新记录不更新值）
     *
     * @Author nece001@163.com
     * @DateTime 2023-08-27
     *
     * @param SimpleSettingEntity $entity
     *
     * @return integer
     */
    public function createOrUpdate(SimpleSettingEntity $entity): int
    {
        if ($entity->id) {
            $item = SimpleSetting::where('id', $entity->id)->find();
        }

        if (!isset($item)) {
            $item = new SimpleSetting();
        }

        $item->is_hidden = $entity->is_hidden;
        $item->is_disabled = $entity->is_disabled;
        $item->is_require = $entity->is_require;
        $item->sort = $entity->sort;
        $item->title = $entity->title;
        $item->note = $entity->note;
        $item->input_type = $entity->input_type;
        $item->value_type = $entity->value_type;
        $item->key_name = $entity->key_name;
        $item->default_value = $entity->default_value;
        $item->options = $entity->options;

        $item->save();


        return $item->id;
    }

    /**
     * 设置值
     *
     * @Author nece001@163.com
     * @DateTime 2023-10-05
     *
     * @param SimpleSettingEntity $entity
     *
     * @return integer
     */
    public function setValue(SimpleSettingEntity $entity): int
    {
        if ($entity->id) {
            $item = SimpleSetting::where('id', $entity->id)->find();
        }

        if ($item) {
            $item->key_value = $entity->key_value;
            $item->save();
            return $entity->id;
        }

        return 0;
    }

    /**
     * 查询一个实体
     *
     * @Author nece001@163.com
     * @DateTime 2023-08-27
     *
     * @param integer $id
     *
     * @return SimpleSettingEntity
     */
    public function find(int $id): SimpleSettingEntity
    {
        $item = SimpleSetting::where('id', $id)->find();
        if ($item) {
            return $this->modelToEntity($item);
        }
        return null;
    }

    /**
     * 用争名查询一个实体
     *
     * @Author nece001@163.com
     * @DateTime 2023-09-02
     *
     * @param string $key_name
     *
     * @return SimpleSettingEntity
     */
    public function findByKeyName(string $key_name): ?SimpleSettingEntity
    {
        $data = Cache::get('__simple_setting_cache__');
        if (true) {
            $data = array();
            $items = SimpleSetting::select();
            foreach ($items as $item) {
                $data[$item->key_name] = $item;
            }
            Cache::set('__simple_setting_cache__', $data);
        }

        if (isset($data[$key_name])) {
            return $this->modelToEntity($data[$key_name]);
        }
        return null;
    }

    /**
     * 分页列表
     *
     * @Author nece001@163.com
     * @DateTime 2023-08-27
     *
     * @param array $params
     * @param \think\Paginator $name
     * @return \Nece\Gears\Paginator
     */
    public function pagedList(array $params): Paginator
    {
        $page = $this->getValue($params, 'page', 1);
        $limit = $this->getValue($params, 'limit', 10);

        $query = SimpleSetting::order('id', 'DESC');

        if ($this->valid($params, 'keyword')) {
            $query->whereLike('title|key_name', '%' . $params['keyword'] . '%');
        }

        $list = $query->paginate($limit, false, array('list_rows' => $page, 'var_page' => 'page'));

        $pager = new Paginator($list->listRows(), $list->total(), $list->currentPage());
        foreach ($list as $item) {
            $pager->addItem($this->modelToEntity($item));
        }

        return $pager;
    }

    /**
     * 模型转实体
     *
     * @Author nece001@163.com
     * @DateTime 2023-08-27
     *
     * @param \think\model $model
     *
     * @return SimpleSettingEntity
     */
    private function modelToEntity($model)
    {
        $entity = new SimpleSettingEntity();

        $entity->id = $model->id;
        $entity->create_time = $model->create_time;
        $entity->update_time = $model->update_time;
        $entity->is_hidden = $model->is_hidden;
        $entity->is_disabled = $model->is_disabled;
        $entity->is_require = $model->is_require;
        $entity->sort = $model->sort;
        $entity->title = $model->title;
        $entity->note = $model->note;
        $entity->input_type = $model->input_type;
        $entity->value_type = $model->value_type;
        $entity->key_name = $model->key_name;
        $entity->key_value = $model->key_value;
        $entity->default_value = $model->default_value;
        $entity->options = $model->options;

        return $entity;
    }
}
