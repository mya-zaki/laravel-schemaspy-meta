<?php
namespace MyaZaki\LaravelSchemaspyMeta\Console;

use Illuminate\Console\Command;
use MyaZaki\LaravelSchemaspyMeta\SchemaMeta;

class GenerateSchemaMetaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schemaspy-meta:generate 
                            {namespace=App : The namespace of Eloquent Models.}
                            {--excludeClass=* : The specified classes are ignored.}
                            {--xmlFile=schemaspy-meta.xml}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate schemaspy-meta.xml from Eloquent Models.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model_namespace = $this->argument('namespace');
        $exclude_classes = $this->option('excludeClass');
        $xml_file = $this->option('xmlFile');

        $composer_json = json_decode(file_get_contents(base_path('composer.json')), true);
        $autoload_psr4 = $composer_json['autoload']['psr-4'];

        $model_path = null;
        foreach ($autoload_psr4 as $ns => $path) {
            if (starts_with($model_namespace, $ns)) {
                $model_path = $path . str_replace('\\', '/', str_after($model_namespace, $ns));
                break;
            }
        }

        if (is_null($model_path)) {
            $this->error('Specified namespace is incorrect.');
            return;
        }
        $this->info('Models path: ' . $model_path);

        if (!is_dir($model_path)) {
            $this->error('Not found the Models path.');
            return;
        }

        $files = glob($model_path . '/*.php');
        if ($files === false) {
            $this->error('Not found PHP files below models path.');
            return;
        }

        $target_files = [];
        foreach ($files as $file) {
            $class = basename($file, '.php');

            $ref = new \ReflectionClass("$model_namespace\\$class");
            if (!$this->isModelClass($ref)) {
                continue;
            }

            if (in_array($class, $exclude_classes)) {
                continue;
            }

            $target_files[] = $file;
        }

        if (empty($target_files)) {
            $this->line('Nothing to generate xml. The models are not found.');
            return;
        }

        $xml_path = base_path() . '/' . $xml_file;

        $result = SchemaMeta::generate($target_files, $model_namespace, $xml_path);

        if ($result === false) {
            $this->error('Failed to write to xml.');
        }
    }

    protected function isModelClass(\ReflectionClass $ref)
    {
        $parent = function ($ref) use (&$parent) {
            $ref = $ref->getParentClass();

            if ($ref === false) {
                return false;
            }

            if ($ref->getName() === \Illuminate\Database\Eloquent\Model::class) {
                return true;
            }

            return $parent($ref);
        };

        return $parent($ref);
    }
}
