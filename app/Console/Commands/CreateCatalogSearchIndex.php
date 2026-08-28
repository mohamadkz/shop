<?php

namespace App\Console\Commands;

use App\Domain\Catalog\Models\Item;
use Elastic\Client\ClientBuilderInterface;
use Illuminate\Console\Command;

class CreateCatalogSearchIndex extends Command
{
    protected $signature = 'catalog:search:create-index
                            {--recreate : Delete and recreate an existing index}';

    protected $description = 'Create the Elasticsearch index for catalog items.';

    public function handle(ClientBuilderInterface $clientBuilder): int
    {
        $client = $clientBuilder->default();
        $index = (new Item())->searchableAs();

        if ($client->indices()->exists(['index' => $index])->asBool()) {
            if (! $this->option('recreate')) {
                $this->warn("Index [{$index}] already exists.");

                return self::SUCCESS;
            }

            $client->indices()->delete(['index' => $index]);
            $this->line("Deleted existing index [{$index}].");
        }

        $client->indices()->create([
            'index' => $index,
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'analysis' => [
                        'analyzer' => [
                            'fa_text' => ['type' => 'persian'],
                        ],
                    ],
                ],
                'mappings' => [
                    'dynamic' => 'strict',
                    'properties' => [
                        'name' => [
                            'type' => 'text',
                            'analyzer' => 'fa_text',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword',
                                    'ignore_above' => 256,
                                ],
                            ],
                        ],
                        'description' => [
                            'type' => 'text',
                            'analyzer' => 'fa_text',
                        ],
                        'category_id' => ['type' => 'integer'],
                        'price' => ['type' => 'scaled_float', 'scaling_factor' => 100],
                        'stock' => ['type' => 'integer'],
                        'status' => ['type' => 'keyword'],
                    ],
                ],
            ],
        ]);

        $this->info("Index [{$index}] created successfully.");

        return self::SUCCESS;
    }
}
