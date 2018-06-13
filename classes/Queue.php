<?php
namespace GoatPen;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class Queue extends Model
{
    protected $casts = [
        'task_id' => 'integer',
        'user_id' => 'integer',
        'file'    => 'string',
        'payload' => 'json',
        'log'     => 'string',
    ];

    protected $dates = [
        'queued',
        'started',
        'completed',
        'created_at',
        'updated_at',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeQueued()
    {
        return $this->whereNotNull('queued')
            ->whereNull('started');
    }

    public function scopeRunning()
    {
        return $this->whereNotNull('started')
            ->whereNull('completed');
    }

    public function run()
    {
        $this->setStarted();

        try {
            $file = TASKS_DIR . DIRECTORY_SEPARATOR . $this->task->script . '.php';

            if (! file_exists($file) || ! is_readable($file)) {
                throw new Exception('Task script not found');
            }

            require $file;

            $this->log('Task completed successfully');
        } catch (Throwable $exception) {
            $this->log(sprintf('Task ended with an error: %s', $exception->getMessage()));
        }

        $this->setCompleted();
    }

    private function setStarted()
    {
        $this->started = Carbon::now();
        $this->save();

        if (ENV === 'dev') {
            echo sprintf("Task '%s' started", $this->task->name) . PHP_EOL;
        }
    }

    private function setCompleted()
    {
        $this->completed = Carbon::now();
        $this->save();

        unlink(UPLOADS_DIR . DIRECTORY_SEPARATOR . $this->file);

        if (ENV === 'dev') {
            echo sprintf("Task '%s' completed", $this->task->name) . PHP_EOL;
        }
    }

    public function log(string $log)
    {
        $this->log .= $log . PHP_EOL;

        if (ENV === 'dev') {
            echo sprintf("LOG: %s", $log) . PHP_EOL;
        }
    }
}
