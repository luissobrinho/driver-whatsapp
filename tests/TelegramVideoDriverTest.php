<?php

namespace Tests;

use Mockery as m;
use BotMan\BotMan\Http\Curl;
use PHPUnit_Framework_TestCase;
use BotMan\BotMan\Messages\Attachments\Video;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BotMan\Drivers\WhatsApp\WhatsAppVideoDriver;
use BotMan\Drivers\WhatsApp\Exceptions\WhatsAppAttachmentException;

class WhatsAppVideoDriverTest extends PHPUnit_Framework_TestCase
{
    private function getDriver($responseData, $htmlInterface = null)
    {
        $request = m::mock(Request::class.'[getContent]');
        $request->shouldReceive('getContent')->andReturn(json_encode($responseData));
        if ($htmlInterface === null) {
            $htmlInterface = m::mock(Curl::class);
        }

        return new WhatsAppVideoDriver($request, [], $htmlInterface);
    }

    /** @test */
    public function it_returns_the_driver_name()
    {
        $driver = $this->getDriver([]);
        $this->assertSame('WhatsAppVideo', $driver->getName());
    }

    /** @test */
    public function it_matches_the_request()
    {
        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'text' => 'Hallo',
            ],
        ]);
        $this->assertFalse($driver->matchesRequest());

        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'document' => [
                    'mime_type' => 'image/png',
                    'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
                ],
            ],
        ]);
        $this->assertFalse($driver->matchesRequest());

        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'video' => [
                    'mime_type' => 'video/quicktime',
                    'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
                ],
            ],
        ]);
        $this->assertTrue($driver->matchesRequest());

        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'video_note' => [
                    'mime_type' => 'video/quicktime',
                    'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
                ],
            ],
        ]);
        $this->assertTrue($driver->matchesRequest());
    }

    private function getRequest($responseData)
    {
        $request = m::mock(\Symfony\Component\HttpFoundation\Request::class.'[getContent]');
        $request->shouldReceive('getContent')->andReturn(json_encode($responseData));

        return $request;
    }

    /** @test */
    public function it_returns_the_message_object()
    {
        $response = new Response('{"result": {"file_path": "foo"}}');
        $htmlInterface = m::mock(Curl::class);
        $htmlInterface->shouldReceive('get')->with('https://api.telegram.org/bot/getFile', [
            'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
        ])->andReturn($response);

        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'video' => [
                    'mime_type' => 'video/quicktime',
                    'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
                ],
            ],
        ], $htmlInterface);
        $this->assertTrue(is_array($driver->getMessages()));
    }

    /** @test */
    public function it_returns_the_message_object_by_reference()
    {
        $response = new Response('{"result": {"file_path": "foo"}}');
        $htmlInterface = m::mock(Curl::class);
        $htmlInterface->shouldReceive('get')->with('https://api.telegram.org/bot/getFile', [
            'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
        ])->andReturn($response);

        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'video' => [
                    'mime_type' => 'video/quicktime',
                    'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
                ],
            ],
        ], $htmlInterface);
        $messages = $driver->getMessages();
        $hash = spl_object_hash($messages[0]);
        $this->assertSame($hash, spl_object_hash($driver->getMessages()[0]));
    }

    /** @test */
    public function it_returns_the_video()
    {
        $response = new Response('{"result": {"file_path": "foo"}}');
        $htmlInterface = m::mock(Curl::class);
        $htmlInterface->shouldReceive('get')->with('https://api.telegram.org/bot/getFile', [
            'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
        ])->andReturn($response);

        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'video' => [
                    'mime_type' => 'video/quicktime',
                    'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
                ],
            ],
        ], $htmlInterface);
        $message = $driver->getMessages()[0];
        $this->assertSame(Video::PATTERN, $message->getText());
        $this->assertSame('https://api.telegram.org/file/bot/foo', $message->getVideos()[0]->getUrl());
        $this->assertSame([
            'mime_type' => 'video/quicktime',
            'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
        ], $message->getVideos()[0]->getPayload());
    }

    /** @test */
    public function it_returns_the_video_from_note()
    {
        $response = new Response('{"result": {"file_path": "foo"}}');
        $htmlInterface = m::mock(Curl::class);
        $htmlInterface->shouldReceive('get')->with('https://api.telegram.org/bot/getFile', [
            'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
        ])->andReturn($response);

        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'video_note' => [
                    'mime_type' => 'video/quicktime',
                    'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
                ],
            ],
        ], $htmlInterface);
        $message = $driver->getMessages()[0];
        $this->assertSame(Video::PATTERN, $message->getText());
        $this->assertSame('https://api.telegram.org/file/bot/foo', $message->getVideos()[0]->getUrl());
        $this->assertSame([
            'mime_type' => 'video/quicktime',
            'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
        ], $message->getVideos()[0]->getPayload());
    }

    /** @test */
    public function it_throws_exception_in_get_attachment_url()
    {
        $response = new Response('{"ok":false,"error_code":400,"description":"Bad Request: file is too big"}', 400);
        $htmlInterface = m::mock(Curl::class);
        $htmlInterface->shouldReceive('get')->with('https://api.telegram.org/bot/getFile', [
            'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
        ])->andReturn($response);

        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'video' => [
                    'mime_type' => 'video/quicktime',
                    'file_id' => 'AgADAgAD6KcxG4tSUUnK3tsu3YsxCu8VSw0ABO72aPxtHuGxcGMFAAEC',
                ],
            ],
        ], $htmlInterface);

        try {
            $driver->getMessages()[0];
        } catch (\Throwable $t) {
            $this->assertSame(WhatsAppAttachmentException::class, get_class($t));
        }
    }

    /** @test */
    public function it_havent_to_match_any_event()
    {
        $driver = $this->getDriver([
            'update_id' => '1234567890',
            'message' => [
                'message_id' => '123',
                'from' => [
                    'id' => 'from_id',
                ],
                'chat' => [
                    'id' => 'chat_id',
                ],
                'date' => '1480369277',
                'text' => 'Hi Julia',
                'new_chat_member' => [
                    'id' => '456',
                    'first_name' => 'Marcel',
                    'last_name' => 'Pociot',
                    'username' => 'mpociot',
                ],
            ],
        ]);
        $this->assertFalse($driver->matchesRequest());
        $this->assertFalse($driver->hasMatchingEvent());
    }
}
