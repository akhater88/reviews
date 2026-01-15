<?php

namespace App\Horizon;

use Illuminate\Support\Collection;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Illuminate\Contracts\Redis\Factory as RedisFactory;

class SafeRedisMasterSupervisorRepository implements MasterSupervisorRepository
{
    /**
     * The Redis connection instance.
     *
     * @var \Illuminate\Contracts\Redis\Factory
     */
    public $redis;

    /**
     * Create a new repository instance.
     *
     * @param  \Illuminate\Contracts\Redis\Factory  $redis
     * @return void
     */
    public function __construct(RedisFactory $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Get the Redis connection instance.
     *
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connection()
    {
        return $this->redis->connection(config('horizon.use'));
    }

    /**
     * Get the names of all the master supervisors currently running.
     *
     * @return Collection
     */
    public function names()
    {
        return collect($this->connection()->smembers('masters'));
    }

    /**
     * Get information on all of the supervisors.
     *
     * @return Collection
     */
    public function all()
    {
        $records = $this->connection()->pipeline(function ($pipe) {
            foreach ($this->names() as $name) {
                $pipe->hmget('master:'.$name, ['name', 'pid', 'status', 'started_at', 'environment']);
            }
        });

        return collect($records)
            ->map(function ($record) {
                // FIX: Handle the case where $record is not an array (corrupted data)
                if (!is_array($record)) {
                    return null;
                }

                $record = array_values($record);

                return ! $record[0] ? null : (object) [
                    'name' => $record[0],
                    'pid' => $record[1],
                    'status' => $record[2],
                    'started_at' => $record[3],
                    'environment' => $record[4],
                ];
            })->filter()->values();
    }

    /**
     * Get information on a master supervisor by name.
     *
     * @param  string  $name
     * @return \stdClass|null
     */
    public function find($name)
    {
        $record = $this->connection()->hmget(
            'master:'.$name, ['name', 'pid', 'status', 'started_at', 'environment']
        );

        // FIX: Handle the case where $record is not an array
        if (!is_array($record)) {
            return null;
        }

        return ! $record['name'] ? null : (object) [
            'name' => $record['name'],
            'pid' => $record['pid'],
            'status' => $record['status'],
            'started_at' => $record['started_at'],
            'environment' => $record['environment'],
        ];
    }

    /**
     * Update the given master supervisor process with the given information.
     *
     * @param  \Laravel\Horizon\MasterSupervisor  $master
     * @return void
     */
    public function update($master)
    {
        $this->connection()->hmset(
            'master:'.$master->name, [
                'name' => $master->name,
                'pid' => $master->pid(),
                'status' => $master->working ? 'running' : 'paused',
                'started_at' => (string) $master->startedAt,
                'environment' => $master->environment,
            ]
        );

        $this->connection()->sadd('masters', $master->name);
        $this->connection()->expire('master:'.$master->name, 15);
    }

    /**
     * Remove the master supervisor information from storage.
     *
     * @param  string  $name
     * @return void
     */
    public function forget($name)
    {
        $this->connection()->del('master:'.$name);
        $this->connection()->srem('masters', $name);
    }

    /**
     * Remove expired master supervisors from storage.
     *
     * @return void
     */
    public function flushExpired()
    {
        $names = $this->names();

        // FIX: Handle case where names might contain corrupted data
        if ($names->isEmpty()) {
            return;
        }

        $records = $this->connection()->pipeline(function ($pipe) use ($names) {
            foreach ($names as $name) {
                $pipe->exists('master:'.$name);
            }
        });

        $names->zip($records)->each(function ($pair) {
            // FIX: Handle the case where $pair values might be corrupted
            if (!is_array($pair) || count($pair) < 2) {
                return;
            }

            [$name, $exists] = $pair;

            if (! $exists) {
                $this->forget($name);
            }
        });
    }
}
