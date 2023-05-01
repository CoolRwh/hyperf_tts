<?php


namespace App\Controller\Api\V1;


use App\Controller\BaseController;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Response;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

/**
 * Class TtsController
 * @package App\Controller\Api
 * @Controller(prefix="api/v1/tts")
 */
class TtsController extends BaseController
{


    /**
     * @Inject()
     * @var Filesystem $filesystem
     */
    protected $filesystem;


    /**
     * @Inject()
     * @var FilesystemFactory $factory
     */
    protected $factory;


    protected static $userList = [
        '1' => ['Name' => 'zh-CN-XiaoxiaoNeural', 'Gender' => 'Female', 'z_name' => '小霞'],  //
        '2' => ['Name' => 'zh-CN-liaoning-XiaobeiNeural', 'Gender' => 'Female', 'z_name' => '辽宁省-小白'], //
        '3' => ['Name' => 'zh-CN-shaanxi-XiaoniNeural', 'Gender' => 'Female', 'z_name' => '山西-小尼'], //
        '4' => ['Name' => 'zh-CN-XiaoyiNeural', 'Gender' => 'Female', 'z_name' => '小易'],
        '5' => ['Name' => 'zh-CN-YunjianNeural', 'Gender' => 'Male', 'z_name' => 'Yunjian'],
        '6' => ['Name' => 'zh-CN-YunxiNeural', 'Gender' => 'Male', 'z_name' => 'Yunxi'],
    ];

    /**
     *@GetMapping(path="")
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface|string
     * @throws FilesystemException
     */
    public function index(RequestInterface $request)
    {

        $data = $request->all();
        if (!isset($data['text']) || trim($data['text']) == ''){
            return "内容不能为空！";
        }
        $text = trim($data['text']);

        $user = $request->input('user');

        try {

            $factory = $this->factory->get('tts');
            $userName = !isset(self::$userList[$request->input('user')]) ? '' : self::$userList[$user]['Name'];
            $fileName = md5($text).'.mp3';
            $path = BASE_PATH.'/public/tts/'.$fileName;
            if (!$factory->fileExists($fileName)) {
                $this->textToMp3($text, $userName, $path);
            }
            $fileData = $factory->read($fileName);
            $response = new Response();
            return $response->withHeader('Content-Type', 'audio/mpeg;charset=utf-8')
                ->withHeader('Connection', "keep-alive")
                ->withHeader('content-disposition', "inline;filename={$fileName}")
                ->withHeader('Cache-Control', 'no-cache')
                ->withBody(new SwooleStream($fileData));
        } catch (\Exception $exception) {
            return $response->json();
        }

    }


    /**
     *
     * @param string $msg
     * @param string $user
     * @param string $path 系统根路径
     * @return string|null
     */
    public function textToMp3($msg = '1', string $user = 'zh-CN-XiaoyiNeural', $path = '')
    {
        $cmd1 = sprintf(
            'edge-tts --voice %s --text "%s" --write-media %s',
            $user,
            $msg,
            $path
        );

        return shell_exec($cmd1);
    }


    /**
     *
     * @return array
     * @GetMapping(path="user")
     */
    public function user()
    {
        return self::$userList;
    }

}
