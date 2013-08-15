<?php

use Symfony\Component\Finder\Finder;

class CptTestSuite extends PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new CptTestSuite();
        $finder = new Finder();

        // ---------- COMMENT OUT TO TEST A SPECIFIC FILE ----------
        // $suite->addTestFile('../src/<yourbundle>/DefaultBundle/Tests/Controller/SomeControllerTest.php');
        // return $suite;
        // ----------

        echo "Searching for test cases...\n\n";
        foreach ($finder->files()->in('../src/')->name('*Test.php') as $file) {
            if (preg_match('%/Tests/[\w-/]+Test.php%i', $file->getPathName())) {
                echo 'Adding test : ' . $file->getPathName() . "\n";
                $suite->addTestFile($file->getPathName());
            }
        }
        echo "\n";

        return $suite;
    }

}