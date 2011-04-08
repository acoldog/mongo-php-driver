<?php
require_once 'PHPUnit/Framework.php';

/**
 * Test class for Mongo.
 * Generated by PHPUnit on 2009-04-09 at 18:09:02.
 */
class MongoTest extends PHPUnit_Framework_TestCase
{
    public function testVersion() {
        $this->assertEquals("1.2.0-", Mongo::VERSION);
    }

    /**
     * @var    Mongo
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    public function setUp() {
        $this->object = new Mongo("localhost", array("connect" => false));
    }


    public function testConnect() {
        $this->object = new Mongo("localhost", false);
        $this->assertFalse($this->object->connected);

        $this->object->connect();
        $this->assertTrue($this->object->connected);

        $this->object->close();
        $this->assertFalse($this->object->connected);

        $this->object->connect();
        $x = $this->object->connect();
        $this->assertTrue($this->object->connected);
        $this->assertTrue($x);
    }

    public function testConnect2() {
        $this->object = new Mongo("localhost", array("connect" => false));
        $this->assertFalse($this->object->connected);
    }

    public function testSpaceChomp() {
      $m = new Mongo("localhost:27018, localhost");
      $m = new Mongo("localhost:27018,    localhost, localhost:27019");
      $m = new Mongo("localhost:27018, localhost, ");
    }

    /**
     * @expectedException MongoConnectionException 
     */
    public function testDumbIPs2() {
	$m = new Mongo(":,:");
    }

    /**
     * @expectedException MongoConnectionException
     */
    public function testDumbIPs3() {
	$m = new Mongo("x:x");
    }

    /**
     * @expectedException MongoConnectionException
     */
    public function testDumbIPs4() {
	$m = new Mongo("localhost:");
    }

    // these should actually work, though
    public function testDumbIPs5() {
	$m = new Mongo("localhost,localhost");
	$m = new Mongo("localhost,localhost:27");
	$m = new Mongo("localhost:27017,localhost:27018,");
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testPersistConnect() {
        $m1 = new Mongo("localhost:27017", false);
        $m1->persistConnect("", "");
        
        $m2 = new Mongo("localhost:27017", false);
        $m2->persistConnect("", "");
        
        // make sure this doesn't disconnect $m2      
        unset($m1);
        
        $c = $m2->selectCollection("foo","bar");
        $c->findOne();
    }

    public function testPersistConnect2() {
        $m1 = new Mongo("localhost:27017", array("persist" => ""));
        $m2 = new Mongo("localhost:27017", array("persist" => ""));
        
        // make sure this doesn't disconnect $m2      
        unset($m1);
      
        $c = $m2->selectCollection("foo","bar");
        $c->setSlaveOkay(true);
        $c->findOne();
    }

    public function test__toString() {
        $this->assertEquals("[localhost:27017]", $this->object->__toString());
        $this->object->connect();
        $this->assertEquals("localhost:27017", $this->object->__toString());        
        
        $m = new Mongo();
        $this->assertEquals("localhost:27017", $m->__toString());
    }

    public function test__toString2() {
        $m = new Mongo("mongodb://localhost:27018,localhost:27017,localhost:27019");
        $this->assertEquals("[localhost:27018],localhost:27017,[localhost:27019]", $m->__toString());
        $m->foo->bar->findOne();
        $this->assertEquals("localhost:27017,[localhost:27018],[localhost:27019]", $m->__toString());        
        $this->assertEquals(51, strlen($m->__toString()));

        // realloc
        $m = new Mongo("mongodb://localhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhosta:27018,localhost:27017");
        $m->phpunit->c->findOne();
        $this->assertEquals("localhost:27017,[localhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhostalocalhosta:27018]", $m->__toString());
        $this->assertEquals(274, strlen($m->__toString()));
    }

    /**
     * @expectedException Exception
     */
    public function testSelectDBException1()
    {
        $db = $this->object->selectDB("");
    }

    /**
     * @expectedException Exception
     */
    public function testSelectDBException2()
    {
        $db = $this->object->selectDB("my database");
    }

    /**
     * @expectedException Exception
     */
    public function testSelectDBException3()
    {
        $db = $this->object->selectDB("x.y.z");
    }

    /**
     * @expectedException Exception
     */
    public function testSelectDBException4()
    {
        $db = $this->object->selectDB(".");
    }

    /**
     * @expectedException Exception
     */
    public function testSelectDBException5()
    {
        $db = $this->object->selectDB(null);
    }

    public function testSelectDB() {
        if (preg_match("/5\.1\../", phpversion())) {
            $this->markTestSkipped("No implicit __toString in 5.1");
            return;
        }

        $db = $this->object->selectDB("foo");
        $this->assertEquals((string)$db, "foo");
        $db = $this->object->selectDB("line\nline");
        $this->assertEquals((string)$db, "line\nline");
        $db = $this->object->selectDB("[x,y]");
        $this->assertEquals((string)$db, "[x,y]");
        $db = $this->object->selectDB(4);
        $this->assertEquals((string)$db, "4");
    }

    /**
     * @expectedException Exception
     */
    public function testSelectCollectionException1()
    {
        $db = $this->object->selectCollection("", "xyz");
    }

    public function testSelectCollection() {
        if (preg_match("/5\.1\../", phpversion())) {
            $this->markTestSkipped("No implicit __toString in 5.1");
            return;
        }

        $c = $this->object->selectCollection("foo", "bar.baz");
        $this->assertEquals((string)$c, "foo.bar.baz");
        $c = $this->object->selectCollection("1", "6");
        $this->assertEquals((string)$c, "1.6"); 
        $c = $this->object->selectCollection("foo", '$cmd');
        $this->assertEquals((string)$c, 'foo.$cmd');
    }

    public function testDropDB() {
        $this->object->connect();
        $c = $this->object->selectCollection("temp", "foo");
        
        $result = $c->db->command(array("ismaster" => 1));
        if (!$result['ismaster']) {
            $this->markTestSkipped("can't test writes on slave");
            return;
        }
        $c->insert(array('x' => 1));

        $this->object->dropDB("temp");
        $this->assertEquals($c->findOne(), NULL);

        $db = $this->object->selectDB("temp");
        $c->insert(array('x' => 1));

        $this->object->dropDB($db);
        $this->assertEquals($c->findOne(), NULL);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testLastError() {
        $m = new Mongo();
        $m->resetError();
        $err = $m->lastError();
        $this->assertEquals(null, $err['err'], json_encode($err));
        $this->assertEquals(0, $err['n'], json_encode($err));
        $this->assertEquals(true, (bool)$err['ok'], json_encode($err));

        $m->forceError();
        $err = $m->lastError();
        $this->assertNotNull($err['err']);
        $this->assertEquals($err['n'], 0);
        $this->assertEquals((bool)$err['ok'], true);
    }

    /**
     * @expectedException PHPUnit_Framework_Error 
     */
    public function testPrevError() {
        $m = new Mongo();
        $m->resetError();
        $err = $m->prevError();
        $this->assertEquals($err['err'], null);
        $this->assertEquals($err['n'], 0);
        $this->assertEquals($err['nPrev'], -1);
        $this->assertEquals((bool)$err['ok'], true);
        
        $m->forceError();
        $err = $m->prevError();
        $this->assertNotNull($err['err']);
        $this->assertEquals($err['n'], 0);
        $this->assertEquals($err['nPrev'], 1);
        $this->assertEquals((bool)$err['ok'], true);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testResetError() {
        $m = new Mongo();
        $m->resetError();
        $err = $m->lastError();
        $this->assertEquals($err['err'], null);
        $this->assertEquals($err['n'], 0);
        $this->assertEquals((bool)$err['ok'], true);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testForceError() {
        $m = new Mongo();
        $m->forceError();
        $err = $m->lastError();
        $this->assertNotNull($err['err']);
        $this->assertEquals($err['n'], 0);
        $this->assertEquals((bool)$err['ok'], true);
    }

    public function testClose() {
        $this->object = new Mongo();
        $this->assertTrue($this->object->connected);

        $this->object->close();
        $this->assertFalse($this->object->connected);

        $this->object->close();
        $this->assertFalse($this->object->connected);
    }

    public function testMongoFormat() {
      $m = new Mongo("mongodb://localhost");
      $m = new Mongo("mongodb://localhost:27017");
      $m = new Mongo("mongodb://localhost:27017,localhost:27018");
      $m = new Mongo("mongodb://localhost:27017,localhost:27018,localhost:27019");
      $m = new Mongo("mongodb://localhost:27018,localhost,localhost:27019");
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testPersistConn() {
      $m1 = new Mongo("localhost", array("persist" => ""));

      // uses the same connection as $m1
      $m2 = new Mongo("localhost", array("persist" => ""));
      $m2->persistConnect();

      // creates a new connection
      $m3 = new Mongo("127.0.0.1", array("persist" => ""));
      $m3->persistConnect();

      // creates a new connection
      $m4 = new Mongo("127.0.0.1:27017", array("persist" => ""));
      $m4->persistConnect();
      
      // creates a new connection
      $m5 = new Mongo("localhost", array("persist" => ""));
      $m5->persistConnect("foo");

      // uses the $m5 connection
      $m6 = new Mongo("localhost", array("persist" => ""));
      $m6->persistConnect("foo");
      
      // uses $md5
      $m7 = new Mongo("localhost", array("persist" => ""));
      $m7->persistConnect("foo", "bar");

      $m8 = new Mongo();
    }

    public function testPersistConn2() {
      $m1 = new Mongo("localhost", array("persist" => ""));

      // uses the same connection as $m1
      $m2 = new Mongo("localhost", array("persist" => ""));
        
      // creates a new connection
      $m3 = new Mongo("127.0.0.1", array("persist" => ""));

      // creates a new connection
      $m4 = new Mongo("127.0.0.1:27017", array("persist" => ""));
      
      // creates a new connection
      $m5 = new Mongo("localhost", array("persist" => "foo"));

      // uses the $m5 connection
      $m6 = new Mongo("localhost", array("persist" => "foo"));
      
      $m8 = new Mongo();
    }

    public function testAuthenticate1() {
      exec("mongo tests/addUser.js", $output, $exit_code);
      if ($exit_code == 0) {
        $m = new Mongo("mongodb://testUser:testPass@localhost");
      }
    }

    public function testAuthenticate2() {
      exec("mongo tests/addUser.js", $output, $exit_code);
      if ($exit_code != 0) {
        $this->markTestSkipped("can't add user");
        return;
      }
      $ok = true;

      try {
        $m = new Mongo("mongodb://testUser:testPa@localhost");
      }
      catch(MongoConnectionException $e) {
        $ok = false;
      }

      $this->assertFalse($ok);
    }

    public function testGetters() {
        if (preg_match("/5\.1\../", phpversion())) {
            $this->markTestSkipped("No implicit __toString in 5.1");
            return;
        }

        $m = new Mongo();
        $db = $m->foo;
        $this->assertTrue($db instanceof MongoDB);
        $this->assertEquals("$db", "foo");
        
        $c = $db->bar;
        $this->assertTrue($c instanceof MongoCollection);
        $this->assertEquals("$c", "foo.bar");
        
        $c2 = $c->baz;
        $this->assertTrue($c2 instanceof MongoCollection);
        $this->assertEquals("$c2", "foo.bar.baz");

        $x = $m->foo->bar->baz;
        $this->assertTrue($x instanceof MongoCollection);
        $this->assertEquals("$x", "foo.bar.baz");
    }


    public function testStatic() {
        $start = memory_get_usage(true);

        for ($i=0; $i<100; $i++) {
          StaticFunctionTest::connect();
        }
        $this->assertEquals($start, memory_get_usage(true));
    }


    public function testListDBs() {
        $m = new Mongo();
        $dbs = $m->listDBs();
        $this->assertEquals(true, (bool)$dbs['ok'], json_encode($dbs));
        $this->assertTrue(array_key_exists('databases', $dbs));
    }

    /*
     * our current test framework can't really test this, so this just passes
     * a couple options and checks things don't explode.
     */
    public function testTimeout() {
      $m = new Mongo("localhost", array("timeout" => 0));
      $m = new Mongo("localhost", array("timeout" => 200000));
      $m = new Mongo("localhost", array("timeout" => -2));
      $m = new Mongo("localhost", array("timeout" => "foo"));
      $m = new Mongo("localhost", array("timeout" => array("x" => 1)));

      $db = $m->phpunit;
      $result = $db->command(array("ismaster" => 1));
      if (!$result['ismaster']) {
        $this->markTestSkipped("can't test writes on slave");
        return;
      }
      
      $c = $db->c;
      $c->drop();
      $c->insert(array("x" => 1));
      $obj = $c->findOne();
      $this->assertEquals(1, $obj['x']);
    }

    /*
     * again, not really testing functionality.
     */
    public function testDB() {
      $m = new Mongo("localhost/foo");
      $m = new Mongo("localhost/bar/baz");
      $m = new Mongo("localhost/");
    }

    /*
     * test with ports
     */
    public function testDBPorts() {
      $m = new Mongo("localhost:27017/foo");
      $m = new Mongo("localhost:27017/bar/baz");
      $m = new Mongo("localhost:27017/");
      $m = new Mongo("localhost:27017,localhost:27019/");
    }

    /*
     * regression
     */
    public function testGetter() {
      if (preg_match("/5\.1\../", phpversion())) {
        $this->markTestSkipped("No implicit __toString in 5.1");
        return;
      }

      $db = $this->object->selectDB('db');
      $this->assertEquals('db', $db->__toString());
      $db = $this->object->selectDB($db);
      $this->assertEquals('db', $db->__toString());
    }

    public function testGetter2() {
      if (preg_match("/5\.1\../", phpversion())) {
        $this->markTestSkipped("No implicit __toString in 5.1");
        return;
      }

      $db = $this->object->__get('db');
      $this->assertEquals('db', $db->__toString());
      $db = $this->object->__get($db);
      $this->assertEquals('db', $db->__toString());
    }

    public function testDomainSock() {
        $os = php_uname("s");
        if (preg_match("/win/i", $os)) {
            $this->markTestSkipped("no domain sockets on windows");
            return;
        }

        try {
            $conn = new Mongo("mongodb:///tmp/mongodb-27017.sock");
            $this->assertEquals(true, $conn->connected);
        
            $conn = new Mongo("mongodb:///tmp/mongodb-27017.sock:0/foo");
            $this->assertEquals(true, $conn->connected);
        }
        catch (MongoConnectionException $e) {
            $this->markTestSkipped("connecting to domain sockets failed: ".$e->getMessage());
        }
    }

    /**
     * @expectedException MongoConnectionException
     */
    public function testDomainSock2() {
        $conn = new Mongo("mongodb:///tmp/foo");
    }

    public function testSlaveOkay1() {
        $conn = new Mongo("mongodb://localhost", array("replicaSet" => true, "slaveOkay" => true));
    }

    public function testPersistStatus() {
        $conn = new Mongo("mongodb://localhost", array("persist" => "chkPS"));
        //        $this->assertEquals($conn->status, "new");
        $conn2 = new Mongo("mongodb://localhost", array("persist" => "chkPS"));
        //        $this->assertEquals($conn2->status, "recycled");
    }

    public function testSlaveOkay() {
        $conn = new Mongo("mongodb://localhost", array("replicaSet" => true, "slaveOkay" => true));
        $this->assertTrue($conn->getSlaveOkay());
        $db = $conn->somedb;
        $this->assertTrue($db->getSlaveOkay());
        $c = $db->somec;
        $this->assertTrue($c->getSlaveOkay());
        
        $conn->setSlaveOkay(false);
        $this->assertFalse($conn->getSlaveOkay());
        $this->assertTrue($db->getSlaveOkay());
        $this->assertTrue($c->getSlaveOkay());

        $db = $conn->somedb;
        $this->assertFalse($db->getSlaveOkay());
        $c = $db->somec;
        $this->assertFalse($c->getSlaveOkay());

        $db->setSlaveOkay(true);
        $this->assertTrue($db->getSlaveOkay());
        
        $c->setSlaveOkay(true);
        $this->assertTrue($c->getSlaveOkay());

        $conn->setSlaveOkay(true);
        $this->assertTrue($conn->getSlaveOkay());
    }
}

class StaticFunctionTest {
  private static $conn = null;

  public static function connect()
  {
    self::$conn = new Mongo;
  }
}

?>
