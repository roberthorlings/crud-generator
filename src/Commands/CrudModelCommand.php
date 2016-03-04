<?php

namespace Appzcoder\CrudGenerator\Commands;

use Illuminate\Console\GeneratorCommand;

class CrudModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:model
                            {name : The name of the model.}
                            {--table= : The name of the table.}
                            {--fillable= : The names of the fillable columns.}
    						{--associations= : Associations for this model. Format is field:type:model.}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudgenerator.custom_template')
        ? config('crudgenerator.path') . '/model.stub'
        : __DIR__ . '/../stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $table = $this->option('table') ?: $this->argument('name');
        $fillable = $this->option('fillable');
        $associations = $this->option('associations');
        
        return $this->replaceNamespace($stub, $name)
            ->replaceTable($stub, $table)
            ->replaceFillable($stub, $fillable)
            ->replaceAssociations($stub, $associations)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the table for the given stub.
     *
     * @param  string  $stub
     * @param  string  $table
     *
     * @return $this
     */
    protected function replaceTable(&$stub, $table)
    {
        $stub = str_replace(
            '{{table}}', $table, $stub
        );

        return $this;
    }

    /**
     * Replace the fillable for the given stub.
     *
     * @param  string  $stub
     * @param  string  $fillable
     *
     * @return $this
     */
    protected function replaceFillable(&$stub, $fillable)
    {
        $stub = str_replace(
            '{{fillable}}', $fillable, $stub
        );

        return $this;
    }

    /**
     * Replace the associations for the given stub.
     *
     * @param  string  $stub
     * @param  string  $associations
     *
     * @return $this
     */
    protected function replaceAssociations(&$stub, $associations)
    {
    	$associationsCode = $this->getAssociationsCode($associations);

    	$stub = str_replace(
    			'{{associations}}', $associationsCode, $stub
    			);
    	
    	return $this;
    }


    /**
     * Returns the PHP code needed for the given associations
     *
     * @param  string  $associations
     *
     * @return $this
     */
    protected function getAssociationsCode($associationDescription)
    {
    	$associations = str_getcsv($associationDescription);
    	return join("\n", array_map(array($this, 'getAssociationCode'), $associations));
    }

    /**
     * Returns the PHP code needed for a single associations
     *
     * @param  string  $association		String specifying the association: foreign_key:type:model[:name]
     *
     * @return $this
     */
    protected function getAssociationCode($association)
    {
    	$parts = explode(':', $association);
    	list($foreignKey, $type, $model) = $parts;
    	
    	// Determine the name of the association
    	if(count($parts) > 3) {
    		$name = $parts[3];
    	} else {
    		$name = $model;
    	}
    	
    	return <<<EOD
    public function $name()
    {
        return \$this->$type('$model', '$foreignKey');
    }    	
EOD;
    }
    
}
