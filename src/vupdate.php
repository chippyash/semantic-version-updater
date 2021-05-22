<?php
declare(strict_types=1);
/**
 * Semantic Version Updater
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson 2017, 2021
 * @license GPLV3.0 See LICENSE.md
 */
include_once __DIR__ . '/../vendor' . '/autoload.php';

use Chippyash\Vupdate\Command\RunCommand;
use Symfony\Component\Console\Application;


$version = '';
$finder = function (string $dir) use (&$version, &$finder) {
    if (file_exists("{$dir}/VERSION")) {
        $version = file_get_contents("{$dir}/VERSION");
        return true;
    }
    $dir = dirname($dir);
    if (empty($dir)) {
        throw new \Exception("Cannot find version file");
    }
    return $finder($dir);
};
$finder(__DIR__);

$app = new Application('vupdate', $version);
$command = new RunCommand();
$app->add($command);
$app->setDefaultCommand($command->getName());
$app->run();
