<?php

$commands = [
    'composer' => 'composer install',
];

$root = dirname(__DIR__);
$envPath = $root . '/.env';
$composerAboutCommand = 'composer about';
$composerInstallCommand = 'composer install';

if(!file_exists($envPath)){
    $envExample = file_get_contents($root . '/.env.example');
    if(!$envExample){
        cout("Unable to retrieve the contents of the .env sample file. Ensure proper permissions have been provided.");
        return;
    }
    if(!file_put_contents($envPath, $envExample)){
        cout("Unable to save the contents of the .env file. Ensure proper permissions have been provided.");
        return;
    }
    cout("A .env file was created in the root project directory. Populate at least the required fields.");
}

if(!str_contains(shell_exec($composerAboutCommand), 'dependency manager')){
    cout('Composer is required to continue - Visit https://getcomposer.org for more information.');
    return;
}
print shell_exec("cd $root && $composerInstallCommand");
print cout();


cout('Finished!');
function cout(string $message = ""): void
{
    print $message . PHP_EOL;
}
