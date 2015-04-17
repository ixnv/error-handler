<?php

namespace eLama\ErrorHandler\Test;

use eLama\ErrorHandler\ContextConverter;

class ContextConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var  resource */
    private static $resource;

    /**
     * @var ContextConverter
     */
    protected $contextConverter;

    public function setUp()
    {
        $this->contextConverter = new ContextConverter();
    }

    public static function tearDownAfterClass()
    {
        if (is_resource(self::$resource)) {
            fclose(self::$resource);
        }
    }

    /**
     * @test
     */
    public function getContext_GivenSimpleValue_ReturnAsIs()
    {
        $this->assertEquals(null, $this->contextConverter->normalize(null));
        $this->assertEquals([], $this->contextConverter->normalize([]));
        $this->assertEquals('a', $this->contextConverter->normalize('a'));
    }

    /**
     * @test
     * @dataProvider context
     */
    public function getContext_GivenArrayWithObjects_ReturnConvertsArray($nesting, $raw, $expected)
    {
        $this->assertEquals($expected, $this->contextConverter->normalize($raw, $nesting));
    }

    public function context()
    {
        $resource = $this->getResource();
        $resourceId = (int)$resource;
        return [
            [
                4,
                [
                    'attr' => [
                        'object' => new FixtureClass($resource)
                    ]
                ],
                [
                    'attr' => [
                        'object' => [
                            '__class_name' => FixtureClass::class,
                            'propertyInt' => 1,
                            'propertyText' => 'qwe',
                            'propertyObject' => [
                                '__class_name' => OneFixtureClass::class,
                                'propertyString' => 'some string',
                                'propertyInt' => 2
                            ],
                            'propertyResource' => '[Resource id #' . $resourceId . ' of type `stream`]'
                        ]
                    ]
                ]
            ],
            [
                3,
                [
                    'attr' => [
                        'object' => new FixtureClass($resource)
                    ]
                ],
                [
                    'attr' => [
                        'object' => [
                            '__class_name' => FixtureClass::class,
                            'propertyInt' => 1,
                            'propertyText' => 'qwe',
                            'propertyObject' => '[object of class `' . OneFixtureClass::class . '`]',
                            'propertyResource' => '[Resource id #' . $resourceId . ' of type `stream`]'
                        ]
                    ]
                ]
            ],
            [
                0,
                [
                    'arr' => [
                        'val' => 1
                    ]
                ],
                '[array(1)]'
            ],
            [
                1,
                [
                    'arr' => [
                        'val' => 1,
                        'val1' => 2
                    ]
                ],
                [
                    'arr' => '[array(2)]'
                ]
            ],
        ];
    }

    protected function getResource()
    {
        if (!self::$resource) {
            self::$resource = fopen(__FILE__, 'r');
        }

        return self::$resource;
    }
}

class FixtureClass
{
    private $propertyInt = 1;

    private $propertyText = 'qwe';

    private $propertyObject = null;

    protected $propertyResource = null;

    public function __construct($resource)
    {
        $this->propertyObject = new OneFixtureClass();
        $this->propertyResource = $resource;
    }
}

class OneFixtureClass
{
    protected $propertyString = 'some string';

    protected $propertyInt = 2;
}
