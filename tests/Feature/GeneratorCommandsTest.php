<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

afterEach(function (): void {
    File::deleteDirectory(app_path('Repositories'));
});

it('generates a repository contract and its eloquent implementation', function (): void {
    $contractPath = app_path('Repositories/Contracts/PostRepository.php');
    $repositoryPath = app_path('Repositories/Eloquent/EloquentPostRepository.php');

    $this->artisan('make:repository', ['model' => 'Post'])
        ->expectsConfirmation('Model [App\\Models\\Post] does not exist. Create it?', 'no')
        ->assertSuccessful();

    expect(File::exists($contractPath))->toBeTrue()
        ->and(File::exists($repositoryPath))->toBeTrue();

    $contract = File::get($contractPath);
    expect($contract)->toContain('namespace App\Repositories\Contracts;')
        ->and($contract)->toContain('interface PostRepository extends RepositoryInterface');

    $repository = File::get($repositoryPath);
    expect($repository)->toContain('namespace App\Repositories\Eloquent;')
        ->and($repository)->toContain('use App\Models\Post;')
        ->and($repository)->toContain('class EloquentPostRepository extends AbstractRepository implements PostRepository')
        ->and($repository)->toContain('return Post::class;');
});

it('supports nested model names', function (): void {
    $repositoryPath = app_path('Repositories/Eloquent/EloquentPostRepository.php');

    $this->artisan('make:repository', ['model' => 'Blog/Post'])
        ->expectsConfirmation('Model [App\\Models\\Blog\\Post] does not exist. Create it?', 'no')
        ->assertSuccessful();

    $repository = File::get($repositoryPath);
    expect($repository)->toContain('use App\Models\Blog\Post;')
        ->and($repository)->toContain('return Post::class;');
});

it('generates a criterion', function (): void {
    $path = app_path('Repositories/Eloquent/Criteria/Completed.php');

    $this->artisan('make:criterion', ['criterion' => 'Completed'])
        ->assertSuccessful();

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)->toContain('namespace App\Repositories\Eloquent\Criteria;')
        ->and($content)->toContain('class Completed implements CriterionInterface');
});

it('generates a nested criterion', function (): void {
    $path = app_path('Repositories/Eloquent/Criteria/Blog/Published.php');

    $this->artisan('make:criterion', ['criterion' => 'Blog/Published'])
        ->assertSuccessful();

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)->toContain('namespace App\Repositories\Eloquent\Criteria\Blog;')
        ->and($content)->toContain('class Published implements CriterionInterface');
});

it('does not overwrite an existing file without confirmation', function (): void {
    $contractPath = app_path('Repositories/Contracts/PostRepository.php');

    $this->artisan('make:repository', ['model' => 'Post'])
        ->expectsConfirmation('Model [App\\Models\\Post] does not exist. Create it?', 'no')
        ->assertSuccessful();

    File::put($contractPath, '<?php // custom');

    $this->artisan('make:repository', ['model' => 'Post'])
        ->expectsConfirmation('Model [App\\Models\\Post] does not exist. Create it?', 'no')
        ->expectsConfirmation('The contract [app/Repositories/Contracts/PostRepository.php] already exists. Overwrite it?', 'no')
        ->expectsConfirmation('The repository [app/Repositories/Eloquent/EloquentPostRepository.php] already exists. Overwrite it?', 'no')
        ->assertSuccessful();

    expect(File::get($contractPath))->toBe('<?php // custom');
});
