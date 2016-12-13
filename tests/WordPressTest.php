<?php

use Boots\Boots;

class WordPressTest extends PHPUnit_Framework_TestCase
{
    protected $boots;

    protected $config;

    protected $appPath;

    protected $appName;

    protected $bootsName = 'boots';

    public function setUp()
    {
        $this->appPath = dirname(dirname(dirname(__FILE__)));
        $this->appName = basename($this->appPath);
        $this->config = [
            'type' => 'plugin',
            'id' => 'boots_test',
            'nick' => 'Boots Test',
            'version' => '0.1.0',
            'logo' => 'logo.png',
            'icon' => '/icon.png',
        ];
        $this->boots = Boots::create($this->appPath, $this->config);
    }

    /** @test */
    public function it_should_throw_exception_if_type_is_invalid()
    {
        $this->setExpectedException('Boots\Exception\UnkownTypeException');
        Boots::create($this->appPath);
    }

    /** @test */
    public function it_should_throw_exception_if_id_not_provided()
    {
        $this->setExpectedException('Boots\Exception\InvalidConfigException');
        Boots::create($this->appPath, ['type' => 'plugin']);
    }

    /** @test */
    public function it_should_throw_exception_if_nick_not_provided()
    {
        $this->setExpectedException('Boots\Exception\InvalidConfigException');
        Boots::create($this->appPath, [
            'type' => 'plugin',
            'id' => 'boots_test',
        ]);
    }

    /** @test */
    public function it_should_throw_exception_if_version_not_provided()
    {
        $this->setExpectedException('Boots\Exception\InvalidConfigException');
        Boots::create($this->appPath, [
            'type' => 'plugin',
            'id' => 'boots_test',
            'nick' => 'Boots Test',
        ]);
    }

    /** @test */
    public function it_should_set_the_default_env()
    {
        $this->assertEquals('production', $this->boots->config()->get('env'));
        $boots = Boots::create($this->appPath, [
            'type' => 'plugin',
            'id' => 'boots_test',
            'nick' => 'Boots Test',
            'version' => '0.1.0',
            'env' => 'local',
        ]);
        $this->assertEquals('local', $boots->config()->get('env'));
    }

    /** @test */
    public function it_should_set_the_type_of_application()
    {
        $this->assertEquals('plugin', $this->boots->config()->get('app.type'));
    }

    /** @test */
    public function it_should_set_the_app_path()
    {
        $this->assertEquals($this->appPath, $this->boots->config()->get('app.path'));
    }

    /** @test */
    public function it_should_set_the_app_url()
    {
        $this->assertEquals(plugins_url($this->appName), $this->boots->config()->get('app.url'));
    }

    /** @test */
    public function it_should_set_the_app_logo()
    {
        $logoUrl = plugins_url($this->appName) . '/' . ltrim($this->config['logo'], '/');
        $this->assertEquals($logoUrl, $this->boots->config()->get('app.logo'));
    }

    /** @test */
    public function it_should_set_the_app_icon()
    {
        $iconUrl = plugins_url($this->appName) . '/' . ltrim($this->config['icon'], '/');
        $this->assertEquals($iconUrl, $this->boots->config()->get('app.icon'));
    }

    /** @test */
    public function it_should_set_the_wp_path()
    {
        $this->assertEquals(rtrim(ABSPATH, '/'), $this->boots->config()->get('wp.path'));
    }

    /** @test */
    public function it_should_set_the_wp_url()
    {
        $this->assertEquals(get_bloginfo('wpurl'), $this->boots->config()->get('wp.url'));
    }

    /** @test */
    public function it_should_set_the_wp_ajax_url()
    {
        $this->assertEquals(admin_url('admin-ajax.php'), $this->boots->config()->get('wp.ajax_url'));
    }

    /** @test */
    public function it_should_set_the_wp_version()
    {
        $this->assertEquals(get_bloginfo('version'), $this->boots->config()->get('wp.version'));
    }

    /** @test */
    public function it_should_set_the_wp_site_url()
    {
        $this->assertEquals(home_url(), $this->boots->config()->get('wp.site_url'));
    }

    /** @test */
    public function it_should_set_the_wp_includes_url()
    {
        $this->assertEquals(rtrim(includes_url(), '/'), $this->boots->config()->get('wp.includes_url'));
    }

    /** @test */
    public function it_should_set_the_wp_content_url()
    {
        $this->assertEquals(content_url(), $this->boots->config()->get('wp.content_url'));
    }

    /** @test */
    public function it_should_set_the_wp_plugins_url()
    {
        $this->assertEquals(plugins_url(), $this->boots->config()->get('wp.plugins_url'));
    }

    /** @test */
    public function it_should_set_the_wp_upload_path()
    {
        $this->assertEquals(wp_upload_dir(), $this->boots->config()->get('wp.uploads'));
    }

    /** @test */
    public function it_should_set_the_wp_admin_url()
    {
        $this->assertEquals(rtrim(admin_url(), '/'), $this->boots->config()->get('wp.admin.url'));
    }

    /** @test */
    public function it_should_set_the_wp_admin_posts_url()
    {
        $this->assertEquals(admin_url('edit.php'), $this->boots->config()->get('wp.admin.posts_url'));
    }

    /** @test */
    public function it_should_set_the_wp_admin_pages_url()
    {
        $this->assertEquals(admin_url('edit.php?post_type=page'), $this->boots->config()->get('wp.admin.pages_url'));
    }

    /** @test */
    public function it_should_set_the_wp_theme_path()
    {
        $this->assertEquals(get_stylesheet_directory(), $this->boots->config()->get('wp.theme.path'));
    }

    /** @test */
    public function it_should_set_the_wp_theme_url()
    {
        $this->assertEquals(get_stylesheet_directory_uri(), $this->boots->config()->get('wp.theme.url'));
    }

    /** @test */
    public function it_should_set_the_wp_theme_parent_path()
    {
        $this->assertEquals(get_template_directory(), $this->boots->config()->get('wp.theme.parent_path'));
    }

    /** @test */
    public function it_should_set_the_wp_theme_parent_url()
    {
        $this->assertEquals(get_template_directory_uri(), $this->boots->config()->get('wp.theme.parent_url'));
    }

    /** @test */
    public function it_should_set_whether_child_theme_is_being_used()
    {
        $config = $this->boots->config();
        $usingChildTheme = $config->get('wp.theme.path') != $config->get('wp.theme.parent_path');
        $this->assertEquals($usingChildTheme, $config->get('wp.using_child_theme'));
    }

    /** @test */
    public function it_should_set_the_boots_version()
    {
        $this->assertEquals('', $this->boots->config()->get('boots.version'));
    }

    /** @test */
    public function it_should_set_the_boots_path()
    {
        $path = "{$this->appPath}/{$this->bootsName}";
        $this->assertEquals($path, $this->boots->config()->get('boots.path'));
    }

    /** @test */
    public function it_should_set_the_boots_extend_path()
    {
        $path = "{$this->appPath}/{$this->bootsName}";
        $this->assertEquals($path . '/extend', $this->boots->config()->get('boots.extend_path'));
    }

    /** @test */
    public function it_should_set_the_boots_url()
    {
        $bootsUrl = plugins_url($this->appName) . "/{$this->bootsName}";
        $this->assertEquals($bootsUrl, $this->boots->config()->get('boots.url'));
    }

    /** @test */
    public function it_should_set_the_boots_extend_url()
    {
        $bootsExtendUrl = $this->boots->config()->get('boots.url') . '/extend';
        $this->assertEquals($bootsExtendUrl, $this->boots->config()->get('boots.extend_url'));
    }

    /** @test */
    public function it_should_set_the_php_version()
    {
        $this->assertEquals(phpversion(), $this->boots->config()->get('php.version'));
    }

    /** @test */
    public function it_should_set_the_php_version_id()
    {
        $this->assertEquals(PHP_VERSION_ID, $this->boots->config()->get('php.version_id'));
    }
}