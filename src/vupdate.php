<?php
/**
 * Semantic Version Updater
 *
 * @author Ashley Kitson
 * @license GPLV3.0 See LICENSE.md
 */
include_once __DIR__ . '/../vendor' . '/autoload.php';

use Zend\Console;
use Chippyash\Validation\Common\Enum;
use Chippyash\Validation\Common\Lambda;
use Chippyash\Validation\Logical\LAnd;
use Chippyash\Type\String\StringType;
use Herrera\Version\Validator as VersionValidator;
use Herrera\Version\Dumper;
use Herrera\Version\Parser;

const VFILENAME = 'VERSION';

/**
 * cli rules
 * @var array
 */
$rules = array(
    'help|h'    => 'Get usage message',
    'file|f=s'  => 'name of version file to use. default = ' . VFILENAME,
    'part|p=s'    => 'Which part of the version to update [patch|feature|bcbreak]. default == patch',
    'version|v=s' => 'Force version number',
    'init|i'      => 'initialise a new version file (name == ' . VFILENAME . ') with V0.0.0'
);
/**
 * @var string
 */
$partToUpdate = 'patch';
/**
 * @var string|null
 */
$forceVersion = null;
/**
 * @var string
 */
$versionFile = VFILENAME;
/**
 * @var string
 */
$version = null;

/**
 * Display help
 * @global Console\Getopt $opts
 */
function help()
{
    global $opts;
    echo basename(__FILE__) . ' V @package-version@' . PHP_EOL;
    echo basename(__FILE__) . ' <options>' . PHP_EOL;
    echo $opts->getUsageMessage();
}

/**
 * Display error & help
 * @param string|array $msg
 * @param int $code
 */
function err($msg, $code)
{
    if (is_array($msg)) {
        $msg = implode(':', $msg);
    }
    echo 'Error: (' . $code . '): ' . $msg . PHP_EOL;
    help();
    exit((int) $code);
}

function validateVersionPart($part)
{
    global $partToUpdate;
    $partVal = new Enum(array('patch','feature','bcbreak'));
    if (!$partVal->isValid($part)) {
        err($partVal->getMessages(), -2);
    }

    $partToUpdate = $part;
}

function validateVersionNumber($number)
{
    $versionVal = new Lambda(function($value) {
        return VersionValidator::isVersion($value);
    },
        new StringType('Not a valid version string')
    );

    if (!$versionVal->isValid($number)) {
        err($versionVal->getMessages(), -3);
    }

    return true;
}

function validateVersionFile($fileName)
{
    global $version;
    $fileVal = new Lambda(function($value) {
        return file_exists($value);
    },
        new StringType('Not a valid version file name')
    );

    $fileContentVal = new Lambda(function($fileName) use (&$version) {
        $version = file_get_contents($fileName);
        return validateVersionNumber($version);
    },
        new StringType('Version file contents are invalid')
    );

    $validator = new LAnd($fileVal, $fileContentVal);
    if (!$validator->isValid($fileName)) {
        err($validator->getMessages(), -3);
    }
}

function updateVersion($version, $fileName)
{
    file_put_contents($fileName, $version);
}

/**
 * Main program
 */
try {
    $opts = new Console\Getopt($rules);
    $opts->parse();
} catch (Console\Exception\RuntimeException $e) {
    err($e->getMessage(), $e->getCode());
}

if ($opts->getOption('h')) {
    help();
    exit(0);
}

if ($opts->getOption('i')) {
    updateVersion('0.0.0', VFILENAME);
    echo "version set to 0.0.0" . PHP_EOL;
    exit(0);
}

//validate options
if ($opts->getOption('p')) {
    validateVersionPart($opts->getOption('p'));
}

if ($opts->getOption('v')) {
    validateVersionNumber($opts->getOption('v'));
    $forceVersion = $opts->getOption('v');
}

//read the version into $version if version file is valid
if ($opts->getOption('f')) {
    $versionFile = $opts->getOption('f');
}
validateVersionFile($versionFile);

echo "current version is $version" . PHP_EOL;

if ($forceVersion) {
    updateVersion($forceVersion, $versionFile);
    echo "version forced to " . $forceVersion . PHP_EOL;
    exit (0);
}

$builder = Parser::toBuilder($version);

switch ($partToUpdate) {
    case 'bcbreak':
        updateVersion(Dumper::toString($builder->incrementMajor()), $versionFile);
        break;
    case 'feature':
        updateVersion(Dumper::toString($builder->incrementMinor()), $versionFile);
        break;
    case 'patch':
        updateVersion(Dumper::toString($builder->incrementPatch()), $versionFile);
        break;
}

echo "version bumped to " . Dumper::toString($builder) . PHP_EOL;
exit(0);



