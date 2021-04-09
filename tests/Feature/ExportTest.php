<?php

namespace Spatie\Export\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Export\Exporter;
use Spatie\Export\ExportServiceProvider;
use Throwable;

class ExportTest extends BaseTestCase
{
    protected const HOME_CONTENT = '<a href="feed/blog.atom" title="all blogposts">Feed</a>Home <a href="about">About</a>';
    protected const ABOUT_CONTENT = 'About';
    protected const FEED_CONTENT = 'Feed';

    protected $distDirectory = __DIR__.DIRECTORY_SEPARATOR.'dist';

    public $baseUrl = 'http://localhost:8080';

    protected function setUp(): void
    {
        parent::setUp();

        if (file_exists($this->distDirectory)) {
            exec(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'del '.$this->distDirectory.' /q'
            : 'rm -r '.$this->distDirectory);
        }

        Route::get('/', function () {
            return static::HOME_CONTENT;
        });

        Route::get('about', function () {
            return static::ABOUT_CONTENT;
        });

        Route::get('feed/blog.atom', function () {
            return static::FEED_CONTENT;
        });
    }

    public function skipIfTestServerIsNotRunning(): void
    {
        try {
            file_get_contents('http://localhost:8080');
        } catch (Throwable $e) {
            $this->markTestSkipped('The testserver is not running.');
        }
    }

    /** @test */
    public function it_crawls_and_exports_routes()
    {
        app(Exporter::class)->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();
    }

    /** @test */
    public function it_exports_paths()
    {
        app(Exporter::class)
            ->crawl(false)
            ->paths(['/', '/about', '/feed/blog.atom'])
            ->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();
    }

    /** @test */
    public function it_exports_urls()
    {
        app(Exporter::class)
            ->crawl(false)
            ->urls([
                'http://localhost:8080/',
                'http://localhost:8080/about',
                'http://localhost:8080/feed/blog.atom'
            ])
            ->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();
    }

    /** @test */
    public function it_exports_mixed()
    {
        app(Exporter::class)
            ->crawl(false)
            ->paths('/')
            ->urls([
                'http://localhost:8080/about',
                'http://localhost:8080/feed/blog.atom'
            ])
            ->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();
    }

    /** @test */
    public function it_exports_included_files()
    {
        app(Exporter::class)
            ->includeFiles([__DIR__.'/../stubs/public' => ''])
            ->export();

        static::assertHomeExists();
        static::assertAboutExists();
        static::assertFeedBlogAtomExists();

        $this->assertFileExists(__DIR__.'/dist/favicon.ico');
        $this->assertFileExists(__DIR__.'/dist/media/image.png');

        $this->assertFalse(file_exists(__DIR__.'/dist/index.php'));
    }

    protected function getPackageProviders($app)
    {
        return [ExportServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('filesystems.disks.export', [
            'driver' => 'local',
            'root' => __DIR__.'/dist',
        ]);

        $app['config']->set('export.disk', 'export');
        $app['config']->set('export.include_files', []);
    }

    protected static function assertHomeExists(): void
    {
        static::assertExportedFile(__DIR__.'/dist/index.html', static::HOME_CONTENT);
    }

    protected static function assertAboutExists(): void
    {
        static::assertExportedFile(__DIR__.'/dist/about/index.html', static::ABOUT_CONTENT);
    }

    protected static function assertFeedBlogAtomExists(): void
    {
        static::assertExportedFile(__DIR__.'/dist/feed/blog.atom', static::FEED_CONTENT);
    }

    protected static function assertExportedFile(string $path, string $content): void
    {
        static::assertFileExists($path);
        static::assertEquals($content, file_get_contents($path));
    }
}
