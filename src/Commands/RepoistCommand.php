<?php

namespace Kurt\Repoist\Commands;

use Illuminate\Console\Command;

class RepoistCommand extends Command 
{
    /**
     * File manager.
     * 
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $fileManager;

	public function __construct()
	{
		parent::__construct();
		
		$this->fileManager = app('files');
	}

	/**
	 * Gets a configuration from package config file.
	 * 
	 * @param  string $key
	 * @return mixed
	 */
    protected function config($key)
    {
        return config('repoist.'.$key);
    }

}
