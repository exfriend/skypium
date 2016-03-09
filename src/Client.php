<?php

namespace Skypium;


use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Client
{

    /**
     * Skype user name
     * @var string
     */
    public $username;
    /**
     * Skype password
     * @var string
     */
    public $password;

    /**
     * @var WebDriver
     */
    protected $driver;
    public $driver_host = 'http://localhost:4444/wd/hub';
    public $screenshot_folder = './screenshots';

    /**
     * Client constructor.
     * @param string $username
     * @param string $password
     * @param null|WebDriver $driver
     */
    public function __construct( $username, $password, WebDriver $driver = null )
    {
        $this->username = $username;
        $this->password = $password;
        if ( !$this->driver = $driver )
        {
            $this->initDriver();
        }
        $this->configureDriver();
    }


    public function screenshot( $name )
    {
        $this->driver->takeScreenshot( $this->screenshot_folder . DIRECTORY_SEPARATOR . $name . '.png' );
    }

    protected function waitForSelector( $selector )
    {
        $total_time = 0;
        $timeout = 30;
        $start = mktime( true );
        while ( $total_time <= $timeout )
        {

            $isPresent = $this->driver->wait()->until( function () use ( $selector )
            {
                return WebDriverExpectedCondition::presenceOfElementLocated( WebDriverBy::cssSelector( $selector ) );
            } );

            try
            {
                $el = $this->driver->findElement( WebDriverBy::cssSelector( $selector ) );
                $isPresent = $el->isDisplayed();
            }
            catch ( NoSuchElementException $e )
            {
                $isPresent = false;
            }

            if ( $isPresent )
            {
                return true;
            }

            $total_time += mktime( true ) - $start;
            usleep( 100 );
        }

        return false;

    }

    public function login()
    {

        $this->driver->get( 'https://web.skype.com' );
        $this->driver->wait()->until( function ()
        {
            return WebDriverExpectedCondition::presenceOfElementLocated( WebDriverBy::id( 'username' ) );
        } );


        $this->driver->findElement( WebDriverBy::id( 'username' ) )->sendKeys( $this->username );
        $this->driver->findElement( WebDriverBy::id( 'password' ) )->sendKeys( $this->password );
        $this->driver->findElement( WebDriverBy::cssSelector( 'form' ) )->submit();

        $this->waitForSelector( '[name=query]' );
        sleep( 15 );
        return true;
    }

    public function getContactStatus( $username )
    {
        $search = $this->driver->findElement( WebDriverBy::cssSelector( '[name=query]' ) );
        $search->clear();
        sleep( 1 );
        $search->sendKeys( $username );
        usleep( 500 );

        $ppl = $this->driver->findElement( WebDriverBy::cssSelector( 'ul.people swx-avatar-deprecated:nth-child(1) > span:nth-child(1)' ) );
        $class = $ppl->getAttribute( 'class' );
        $status = str_replace( 'tile ', '', $class );
        return $status;
    }

    private function initDriver()
    {
        $this->driver = RemoteWebDriver::create( $this->driver_host, DesiredCapabilities::chrome() );
    }

    private function configureDriver()
    {
        $this->driver->manage()->timeouts()->implicitlyWait( 15 );
    }


}