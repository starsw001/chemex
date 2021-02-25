<?php

namespace App\Admin\Forms;

use App\Admin\Repositories\DeviceRecord;
use App\Models\ColumnSort;
use App\Models\CustomColumn;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Exception;


class DeviceColumnDeleteForm extends Form
{
    use LazyWidget;

    /**
     * 处理表单提交逻辑
     * @param array $input
     * @return JsonResponse
     */
    public function handle(array $input): JsonResponse
    {
        $id = $input['custom_field_id'] ?? null;

        // 如果没有设备id或者归还时间或者归还描述则返回错误
        if (!$id) {
            return $this->response()
                ->error(trans('main.parameter_missing'));
        }

        try {
            $table_name = (new DeviceRecord())->getTable();
            $custom_column = CustomColumn::find($id);

            if (empty($custom_column)) {
                return $this->response()
                    ->error(trans('main.record_none'));
            }

            $column_sort = ColumnSort::where('table_name', $table_name)
                ->where('field', $custom_column->name)
                ->first();
            if (!empty($column_sort)) {
                $column_sort->delete();
            }

            $custom_column->delete();

            return $this->response()
                ->success(trans('main.success'))
                ->refresh();
        } catch (Exception $exception) {
            return $this->response()
                ->error(trans('main.fail') . '：' . $exception->getMessage());
        }
    }

    /**
     * 构造表单
     */
    public function form()
    {
        $this->select('custom_field_id')
            ->options(CustomColumn::where('table_name', (new DeviceRecord())->getTable())
                ->pluck('name', 'id'))
            ->required();
    }
}
