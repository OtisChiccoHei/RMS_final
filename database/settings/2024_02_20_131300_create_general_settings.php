<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.brand_name', 'SuperDuper Starter Kit');
        $this->migrator->add('general.brand_logo', 'sites/logo.png');
        $this->migrator->add('general.brand_logoHeight', '3rem');
        $this->migrator->add('general.site_active', true);
        $this->migrator->add('general.site_favicon', 'sites/logo.ico');
        $this->migrator->add('general.site_theme', [
            "primary" => "#6E6DD7",
            "secondary" => "#e8aa3a",
            "gray" => "#485173",
            "success" => "#1DCB8A",
            "danger" => "#ff5467",
            "info" => "#1fd2de",
            "warning" => "#f5de8d",
        ]);
    }
};
