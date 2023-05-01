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
     *
     * @GetMapping(path="")
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws FilesystemException
     */
    public function index(RequestInterface $request)
    {

        $response = new Response();
        $msg = $request->input('msg', '你好！');

        $user = $request->input('user', '');

        $userName = !isset(self::$userList[$user]) ? '' : self::$userList[$user]['Name'];

        $fileName = date('Ymd-Hi').'-'.uniqid().'.mp3';

        $path = BASE_PATH.'/public/tts/'.$fileName;


        try {

            $this->textToMp3($msg, $userName, $path);
            $factory = $this->factory->get('tts');

            $fileData = $factory->read($fileName);

            return $response->withHeader('content-type', 'audio/mpeg')
                ->withHeader('content-disposition', "inline;filename={$fileName}")
                ->withHeader('Cache-Control', 'no-cache')
                ->withHeader('Cache-Control', 'no-chunked')
                ->withBody(new SwooleStream($fileData));
        } catch (\Exception $exception) {
            return $response->json(['error' => $exception->getMessage()]);
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
//        return exec($cmd1.' 2>&1',$output,$status);
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
