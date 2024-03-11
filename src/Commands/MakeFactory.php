<?php

namespace Lunarstorm\LaravelDDD\Commands;

use Lunarstorm\LaravelDDD\Support\Domain;
use Lunarstorm\LaravelDDD\Support\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeFactory extends DomainGeneratorCommand
{
    protected $name = 'ddd:factory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a domain model factory';

    protected $type = 'Factory';

    protected function getArguments()
    {
        return [
            ...parent::getArguments(),

            new InputArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the factory',
            ),
        ];
    }

    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The name of the model'],
        ];
    }

    protected function getStub()
    {
        return $this->resolveStubPath('factory.php.stub');
    }

    protected function rootNamespace()
    {
        return 'Database\\Factories\\';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        $domain = $this->getDomain();

        return $rootNamespace.'\\'.$domain;
    }

    protected function getRelativeDomainNamespace(): string
    {
        return '';
    }

    protected function getPath($name)
    {
        if (! str_ends_with($name, 'Factory')) {
            $name .= 'Factory';
        }

        $name = str($name)
            ->replaceFirst($this->rootNamespace(), '')
            ->replace('\\', '/')
            ->ltrim('/')
            ->append('.php')
            ->toString();

        return Path::normalize(base_path('database/factories/'.$name));
    }

    protected function getFactoryName()
    {
        $name = $this->getNameInput();

        return str_ends_with($name, 'Factory')
            ? substr($name, 0, -7)
            : $name;
    }

    protected function preparePlaceholders(): array
    {
        $domain = new Domain($this->getDomain());

        $name = $this->getNameInput();

        $modelName = $this->option('model') ?: $this->guessModelName($name);

        $domainModel = $domain->model($modelName);

        $domainFactory = $domain->factory($name);

        return [
            'namespacedModel' => $domainModel->fqn,
            'model' => class_basename($domainModel->fqn),
            'factory' => $this->getFactoryName(),
            'namespace' => $domainFactory->namespace,
        ];
    }

    protected function guessModelName($name)
    {
        if (str_ends_with($name, 'Factory')) {
            $name = substr($name, 0, -7);
        }

        return (new Domain($this->getDomain()))->model($name)->name;
    }
}
