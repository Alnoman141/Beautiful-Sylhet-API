<?php
/**
 *Name: Abdullah Al Noman
 *Designation: Sr. Software Engineer
 *Date: 2025-04-08 17:22
 */

namespace App\Traits;

use App\Models\ModelLog;

trait LogsModelChanges
{
    public static function bootLogsModelChanges()
    {
        static::created(function ($model) {
            $model->logModelEvent('created', $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = collect($model->getChanges())
                ->forget('updated_at')
                ->mapWithKeys(function ($new, $key) use ($model) {
                    return [
                        $key => [
                            'old' => $model->getOriginal($key),
                            'new' => $new,
                        ],
                    ];
                });

            if ($changes->isNotEmpty()) {
                $model->logModelEvent('updated', $changes);
            }
        });

        static::deleted(function ($model) {
            $model->logModelEvent('deleted', $model->getOriginal());
        });
    }

    protected function logModelEvent($action, $changes)
    {
        ModelLog::create([
            'model_type' => static::class,
            'model_id' => $this->id,
            'action' => $action,
            'changes' => $changes,
            'user_id' => auth()->id(),
        ]);
    }
}
