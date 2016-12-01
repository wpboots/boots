<?php

use Boots\Boots;

class Api_2_0_0_Test extends PHPUnit_Framework_TestCase
{
    protected $boots;

    protected $config;

    protected $abspath;

    protected $appPath;

    protected $appName;

    protected $bootsName = 'boots';

    // For another plugin

    protected $boots2;

    protected $abspath2;

    protected $appPath2;

    protected $appName2;

    public function setUp()
    {
        $this->appPath = dirname(dirname(dirname(__FILE__)));
        $this->appName = basename($this->appPath);
        $this->abspath = "{$this->appPath}/index.php";
        $this->config = [
            'abspath' => $this->abspath,
            'id' => 'boots_test',
            'nick' => 'Boots Test',
            'version' => '0.1.0',
            'logo' => 'logo.png',
            'icon' => '/icon.png',
        ];

        $this->boots = new Boots('plugin', $this->config);

        // Another plugin
        $this->appName2 = 'another-plugin';
        $this->appPath2 = __DIR__ . "/{$this->appName2}";
        $this->abspath2 = "{$this->appPath2}/plugin.php";
        $this->boots2 = new Boots('plugin', array_replace($this->config, [
            'abspath' => $this->abspath2,
            'id' => 'boots_test_2',
            'nick' => 'Boots Test 2',
        ]));
    }

    /** @test */
    public function it_should_throw_exception_if_type_is_invalid()
    {
        $this->setExpectedException('Boots\Exception\InvalidTypeException');
        new Boots(null, ['abspath' => $this->abspath]);
    }

    /** @test */
    public function it_should_throw_exception_if_id_not_provided()
    {
        $this->setExpectedException('Boots\Exception\InvalidConfigException');
        new Boots('plugin', ['abspath' => $this->abspath]);
    }

    /** @test */
    public function it_should_throw_exception_if_nick_not_provided()
    {
        $this->setExpectedException('Boots\Exception\InvalidConfigException');
        new Boots('plugin', [
            'abspath' => $this->abspath,
            'id' => 'boots_test',
        ]);
    }

    /** @test */
    public function it_should_throw_exception_if_version_not_provided()
    {
        $this->setExpectedException('Boots\Exception\InvalidConfigException');
        new Boots('plugin', [
            'abspath' => $this->abspath,
            'id' => 'boots_test',
            'nick' => 'Boots Test',
        ]);
    }

    /** @test */
    public function it_should_set_the_default_env()
    {
        $this->assertEquals('production', $this->boots->getConfig()->get('env'));
        $boots = new Boots('plugin', [
            'abspath' => $this->abspath,
            'id' => 'boots_test',
            'nick' => 'Boots Test',
            'version' => '0.1.0',
            'env' => 'local',
        ]);
        $this->assertEquals('local', $boots->getConfig()->get('env'));
    }

    /** @test */
    public function it_should_set_the_type_of_application()
    {
        $this->assertEquals('plugin', $this->boots->getConfig()->get('app.type'));
    }

    /** @test */
    public function it_should_set_the_app_path()
    {
        $this->assertEquals($this->appPath, $this->boots->getConfig()->get('app.path'));
    }

    /** @test */
    public function it_should_set_the_app_url()
    {
        $this->assertEquals(plugins_url($this->appName), $this->boots->getConfig()->get('app.url'));
    }

    /** @test */
    public function it_should_set_the_app_logo()
    {
        $logoUrl = plugins_url($this->appName) . '/' . ltrim($this->config['logo'], '/');
        $this->assertEquals($logoUrl, $this->boots->getConfig()->get('app.logo'));
    }

    /** @test */
    public function it_should_set_the_app_icon()
    {
        $iconUrl = plugins_url($this->appName) . '/' . ltrim($this->config['icon'], '/');
        $this->assertEquals($iconUrl, $this->boots->getConfig()->get('app.icon'));
    }

    /** @test */
    public function it_should_set_the_wp_path()
    {
        $this->assertEquals(rtrim(getenv('ABSPATH'), '/'), $this->boots->getConfig()->get('wp.path'));
    }

    /** @test */
    public function it_should_set_the_wp_url()
    {
        $this->assertEquals(get_bloginfo('wpurl'), $this->boots->getConfig()->get('wp.url'));
    }

    /** @test */
    public function it_should_set_the_wp_ajax_url()
    {
        $this->assertEquals(admin_url('admin-ajax.php'), $this->boots->getConfig()->get('wp.ajax_url'));
    }

    /** @test */
    public function it_should_set_the_wp_version()
    {
        $this->assertEquals(get_bloginfo('version'), $this->boots->getConfig()->get('wp.version'));
    }

    /** @test */
    public function it_should_set_the_wp_site_url()
    {
        $this->assertEquals(home_url(), $this->boots->getConfig()->get('wp.site_url'));
    }

    /** @test */
    public function it_should_set_the_wp_includes_url()
    {
        $this->assertEquals(rtrim(includes_url(), '/'), $this->boots->getConfig()->get('wp.includes_url'));
    }

    /** @test */
    public function it_should_set_the_wp_content_url()
    {
        $this->assertEquals(content_url(), $this->boots->getConfig()->get('wp.content_url'));
    }

    /** @test */
    public function it_should_set_the_wp_plugins_url()
    {
        $this->assertEquals(plugins_url(), $this->boots->getConfig()->get('wp.plugins_url'));
    }

    /** @test */
    public function it_should_set_the_wp_upload_path()
    {
        $this->assertEquals(wp_upload_dir(), $this->boots->getConfig()->get('wp.uploads'));
    }

    /** @test */
    public function it_should_set_the_wp_admin_url()
    {
        $this->assertEquals(rtrim(admin_url(), '/'), $this->boots->getConfig()->get('wp.admin.url'));
    }

    /** @test */
    public function it_should_set_the_wp_admin_posts_url()
    {
        $this->assertEquals(admin_url('edit.php'), $this->boots->getConfig()->get('wp.admin.posts_url'));
    }

    /** @test */
    public function it_should_set_the_wp_admin_pages_url()
    {
        $this->assertEquals(admin_url('edit.php?post_type=page'), $this->boots->getConfig()->get('wp.admin.pages_url'));
    }

    /** @test */
    public function it_should_set_the_wp_theme_path()
    {
        $this->assertEquals(get_stylesheet_directory(), $this->boots->getConfig()->get('wp.theme.path'));
    }

    /** @test */
    public function it_should_set_the_wp_theme_url()
    {
        $this->assertEquals(get_stylesheet_directory_uri(), $this->boots->getConfig()->get('wp.theme.url'));
    }

    /** @test */
    public function it_should_set_the_wp_theme_parent_path()
    {
        $this->assertEquals(get_template_directory(), $this->boots->getConfig()->get('wp.theme.parent_path'));
    }

    /** @test */
    public function it_should_set_the_wp_theme_parent_url()
    {
        $this->assertEquals(get_template_directory_uri(), $this->boots->getConfig()->get('wp.theme.parent_url'));
    }

    /** @test */
    public function it_should_set_whether_child_theme_is_being_used()
    {
        $config = $this->boots->getConfig();
        $usingChildTheme = $config->get('wp.theme.path') != $config->get('wp.theme.parent_path');
        $this->assertEquals($usingChildTheme, $config->get('wp.using_child_theme'));
    }

    /** @test */
    public function it_should_set_the_boots_version()
    {
        $this->assertEquals($this->boots->getVersion(), $this->boots->getConfig()->get('boots.version'));
    }

    /** @test */
    public function it_should_set_the_boots_path()
    {
        $path = "{$this->appPath}/{$this->bootsName}";
        $this->assertEquals($path, $this->boots->getConfig()->get('boots.path'));

        // Another plugin
        $path = "{$this->appPath2}/{$this->bootsName}";
        $this->assertEquals($path, $this->boots2->getConfig()->get('boots.path'));
    }

    /** @test */
    public function it_should_set_the_boots_extend_path()
    {
        $path = "{$this->appPath}/{$this->bootsName}";
        $this->assertEquals($path . '/extend', $this->boots->getConfig()->get('boots.extend_path'));

        // Another plugin
        $path = "{$this->appPath2}/{$this->bootsName}";
        $this->assertEquals($path . '/extend', $this->boots2->getConfig()->get('boots.extend_path'));
    }

    /** @test */
    public function it_should_set_the_boots_url()
    {
        $bootsUrl = plugins_url($this->appName) . "/{$this->bootsName}";
        $this->assertEquals($bootsUrl, $this->boots->getConfig()->get('boots.url'));
    }

    /** @test */
    public function it_should_set_the_boots_extend_url()
    {
        $bootsExtendUrl = $this->boots->getConfig()->get('boots.url') . '/extend';
        $this->assertEquals($bootsExtendUrl, $this->boots->getConfig()->get('boots.extend_url'));
    }

    /** @test */
    public function it_should_set_the_php_version()
    {
        $this->assertEquals(phpversion(), $this->boots->getConfig()->get('php.version'));
    }

    /** @test */
    public function it_should_set_the_php_version_id()
    {
        $this->assertEquals(PHP_VERSION_ID, $this->boots->getConfig()->get('php.version_id'));
    }
}