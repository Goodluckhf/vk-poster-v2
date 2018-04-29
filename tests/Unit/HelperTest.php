<?php
namespace Tests\Unit;

use Tests\TestCase;
use \App\Helpers\Helper;

class HelperTest extends TestCase {
	
	public function testGroupForVkApiByHrefCanReturnOwnerId() {
		$result = Helper::groupForVkApiByHref('https://vk.com/club148143523');
		$expectedResult = -148143523;
		$this->assertEquals($expectedResult, $result['owner_id']);
		
		$result = Helper::groupForVkApiByHref('club223143523');
		$expectedResult = -223143523;
		$this->assertEquals($expectedResult, $result['owner_id']);
		
		$result = Helper::groupForVkApiByHref('vk.com/club31222');
		$expectedResult = -31222;
		$this->assertEquals($expectedResult, $result['owner_id']);
	}
	
	public function testGroupForVkApiByHrefCanReturnDomain() {
		$result = Helper::groupForVkApiByHref('https://vk.com/nice.advice');
		$expectedResult = 'nice.advice';
		$this->assertEquals($expectedResult, $result['domain']);
		
		$result = Helper::groupForVkApiByHref('nice.advice');
		$expectedResult = 'nice.advice';
		$this->assertEquals($expectedResult, $result['domain']);
		
		$result = Helper::groupForVkApiByHref('https://vk.com/club.lol');
		$expectedResult = 'club.lol';
		$this->assertEquals($expectedResult, $result['domain']);
	}
	
	public function testGroupIdForLink() {
		$result = Helper::groupIdForLink(12345);
		$this->assertEquals(12345, $result);
		
		$result = Helper::groupIdForLink(-2234);
		$this->assertEquals(2234, $result);
	}
	
	public function testHrefByGroupObjVkCanRecieveOwnerId() {
		$result = Helper::hrefByGroupObjVk(['owner_id' => 1234]);
		$this->assertEquals('https://vk.com/club1234', $result);
		
		$result = Helper::hrefByGroupObjVk(['owner_id' => -1234]);
		$this->assertEquals('https://vk.com/club1234', $result);
	}
	
	public function testHrefByGroupObjVkCanRecieveDomain() {
		$result = Helper::hrefByGroupObjVk(['domain' => "testid"]);
		$this->assertEquals('https://vk.com/testid', $result);
	}
	
	public function testAddProtocolIfStringHasProtocolReturnIt() {
		$result = Helper::addProtocol('http://lol.ru');
		$this->assertEquals('http://lol.ru', $result);
		
		$result = Helper::addProtocol('https://test.ru?id=1');
		$this->assertEquals('https://test.ru?id=1', $result);
	}
	
	public function testAddProtocolIfStringHasNotProtocolAdd() {
		$result = Helper::addProtocol('lol.ru');
		$this->assertEquals('http://lol.ru', $result);
		
		$result = Helper::addProtocol('test-url.ru?id=123');
		$this->assertEquals('http://test-url.ru?id=123', $result);
	}
	
	public function testParamFromUrlStrReturnNullIfThereIsNoQuery() {
		$result = Helper::paramFromUrlStr('lol.ru', 'param');
		$this->assertEquals(null, $result);
	}
	
	public function testParamFromUrlStrReturnNullIfThereIsNoParam() {
		$result = Helper::paramFromUrlStr('lol.ru?test=1', 'paramName');
		$this->assertEquals(null, $result);
	}
	
	public function testParamFromUrlStrReturnParamValue() {
		$result = Helper::paramFromUrlStr('lol.ru?paramName=1', 'paramName');
		$this->assertEquals(1, $result);
	}
}