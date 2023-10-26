<?php

namespace Webkul\Faker\Commands\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Webkul\Faker\Jobs\Faker as FakerJob;

class Faker extends Command
{
    /**
     * The entity to create.
     *
     * @var string
     */
    protected $entity = null;

    /**
     * Product type to create
     *
     * @var string
     */
    protected $productType = null;

    /**
     * Number of records to create
     *
     * @var int|array
     */
    protected $count = null;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bagisto:fake';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates fake records for testing';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->promptForChoices();

        if (! $this->entity) {
            $this->promptForLimit('customers');

            $this->promptForLimit('categories');

            $this->promptForLimit('products');
        } else {
            $this->promptForLimit();
        }

        $this->createRecords();
    }

    /**
     * Prompt for which records to create.
     *
     * @return void
     */
    protected function promptForChoices()
    {
        $choices = $this->getChoices();

        $choice = $this->choice('Which record would you like to create?', $choices);

        if ($choice == $choices[0] || is_null($choice)) {
            return;
        }

        if (in_array($choice, array_slice($choices, 1, 2))) {
            $this->entity = strip_tags($choice);

            return;
        }

        $this->parseChoice($choice);
    }

    /**
     * Prompt for the limit to create records.
     *
     * @param  string  $$entity
     * @return void
     */
    protected function promptForLimit($entity = null)
    {
        if ($entity) {
            $count = $this->ask('Please enter the count for ' . $entity);
        } else {
            $count = $this->ask('Please enter the count');
        }

        if (! is_numeric($count)) {
            $this->warn('Warning: limit must be a number.');

            return $this->promptForLimit($entity);
        }

        if ($count <= 0) {
            $this->warn('Warning: limit must be greater then 0.');

            return $this->promptForLimit($entity);
        }

        if ($entity) {
            $this->count[$entity] = $count;
        } else {
            $this->count = $count;
        }
    }

    /**
     * The choices available via the prompt.
     *
     * @return array
     */
    public function getChoices()
    {
        return [
            '<comment>Create all from the following</comment>',
            '<comment>Customers</comment>',
            '<comment>Categories</comment>',
            '<comment>Products: </comment>All',
            '<comment>Products: </comment>Simple',
            '<comment>Products: </comment>Virtual',
            '<comment>Products: </comment>Downloadable',
            '<comment>Products: </comment>Configurable',
        ];
    }

    /**
     * Parse the answer that was given via the prompt.
     *
     * @param  string  $choice
     * @return void
     */
    protected function parseChoice($choice)
    {
        [$this->entity, $this->productType] = explode(': ', strip_tags($choice));
    }

    /**
     * Create selected records
     *
     * @return void
     */
    protected function createRecords()
    {
        if ($this->entity) {
            $this->createBatches($this->entity, $this->count, $this->productType);
        } else {
            $this->createBatches('customers', $this->count['customers']);

            $this->createBatches('categories', $this->count['categories']);

            $this->createBatches('products', $this->count['products']);
        }
    }

    /**
     * Create selected records
     *
     * @param  string  $entity
     * @param  int  $count
     * @param  string  $productType
     * @return void
     */
    protected function createBatches(
        $entity,
        $count,
        $productType = null
    ) {
        $batch = Bus::batch([])->dispatch();

        do {
            if ($count >= 10) {
                $count -= ($process = 10);
            } else {
                $process = $count;

                $count = 0;
            }

            $batch->add(new FakerJob(strtolower($entity), $process, $productType));
        } while ($count > 0);
    }
}
