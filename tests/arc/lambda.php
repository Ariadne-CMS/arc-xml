<?php

    /*
     * This file is part of the Ariadne Component Library.
     *
     * (c) Muze <info@muze.nl>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    require_once( __DIR__.'/../bootstrap.php' );
     
    class TestLambda extends UnitTestCase {

        function testPrototype() {
            $view = \arc\lambda::prototype( [
                'foo' => 'bar',
                'bar' => function() {
                    return $this->foo;
                }
            ] );
            $this->assertTrue( $view->foo === 'bar' );
            $this->assertTrue( $view->bar() === 'bar' );
        }

        function testPrototypeInheritance() {
            $foo = \arc\lambda::prototype( [
                'foo' => 'bar',
                'bar' => function() {
                    return $this->foo;
                }
            ]);
            $bar = $foo->extend( [
                'foo' => 'rab'
            ]);
            $this->assertTrue( $foo->foo === 'bar' );
            $this->assertTrue( $bar->foo === 'rab' );
            $this->assertTrue( $foo->bar() === 'bar' );
            $this->assertTrue( $bar->bar() === 'rab' );
            $this->assertTrue( $bar->hasOwnProperty('foo') );
            $this->assertFalse( $bar->hasOwnProperty('bar') );

        }

        function testPrototypeInheritance2() {
            $foo = \arc\lambda::prototype([
                'bar' => function() {
                    return 'bar';
                }
            ]);
            $bar = $foo->extend([
                'bar' => function() use ( $foo ) {
                    return 'foo'.$foo->bar();
                }
            ]);
            $this->assertTrue( $bar->bar() === 'foobar' );
        }

        function testPrototypeInheritance3() {
            $foo = \arc\lambda::prototype([
                'bar' => function() {
                    return 'bar';
                },
                'foo' => function() {
                    return '<b>'.$this->bar().'</b>';
                }
            ]);
            $bar = $foo->extend([
                'bar' => function() use ( $foo ) {
                    return 'foo'.$foo->bar();
                }
            ]);
            $this->assertTrue( $bar->foo() === '<b>foobar</b>' );
        }

    }
