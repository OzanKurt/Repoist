<?php

declare(strict_types=1);

use App\Repositories\Contracts\FooRepository;
use App\Repositories\Eloquent\EloquentFooRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Kurt\Repoist\Support\RepositoryBinder;

afterEach(function (): void {
    File::deleteDirectory(app_path('Repositories'));
});

it('binds a contract to its eloquent implementation by convention', function (): void {
    $contractDir = app_path('Repositories/Contracts');
    $eloquentDir = app_path('Repositories/Eloquent');

    File::ensureDirectoryExists($contractDir);
    File::ensureDirectoryExists($eloquentDir);

    File::put($contractDir.'/FooRepository.php', <<<'PHP'
        <?php

        namespace App\Repositories\Contracts;

        use Kurt\Repoist\Repositories\Contracts\RepositoryInterface;

        interface FooRepository extends RepositoryInterface {}
        PHP);

    File::put($eloquentDir.'/EloquentFooRepository.php', <<<'PHP'
        <?php

        namespace App\Repositories\Eloquent;

        use App\Repositories\Contracts\FooRepository;
        use Kurt\Repoist\Repositories\Eloquent\AbstractRepository;
        use Kurt\Repoist\Tests\Stubs\Post;

        class EloquentFooRepository extends AbstractRepository implements FooRepository
        {
            public function entity(): string
            {
                return Post::class;
            }
        }
        PHP);

    require $contractDir.'/FooRepository.php';
    require $eloquentDir.'/EloquentFooRepository.php';

    (new RepositoryBinder($this->app, $this->app->make(Filesystem::class)))->bind();

    expect($this->app->bound(FooRepository::class))->toBeTrue()
        ->and($this->app->make(FooRepository::class))
        ->toBeInstanceOf(EloquentFooRepository::class);
});
