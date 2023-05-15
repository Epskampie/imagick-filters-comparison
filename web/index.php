<?php
include __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\Slim();
$app->config('debug', true);

$app->get('/', function () use ($app) {
    $url = $_GET['url'] ?? '';
    $width = $_GET['width'] ?? 200;
    
    if (!$url) {
        echo '?url=...&width=200';
        die;
    }
    $imageBlob = HttpClient::from()->get($url);
    
    $imagick = new Imagick();
    $imagick->readImageBlob($imageBlob);
    
    $qualities = [75, 80, 85, 90, 95, 100];
    $filters = [
        'scaleImage' => 'scaleImage',
        // 'adaptiveResizeImage' => 'adaptiveResizeImage',
        // "POINT" => Imagick::FILTER_POINT,
        // "BOX" => Imagick::FILTER_BOX,
        // "TRIANGLE" => Imagick::FILTER_TRIANGLE,
        "HERMITE" => Imagick::FILTER_HERMITE,
        "HANNING" => Imagick::FILTER_HANNING,
        "HAMMING" => Imagick::FILTER_HAMMING,
        "BLACKMAN" => Imagick::FILTER_BLACKMAN,
        "GAUSSIAN" => Imagick::FILTER_GAUSSIAN,
        "QUADRATIC" => Imagick::FILTER_QUADRATIC,
        "CUBIC" => Imagick::FILTER_CUBIC,
        "CATROM" => Imagick::FILTER_CATROM,
        "MITCHELL" => Imagick::FILTER_MITCHELL,
        "JINC" => Imagick::FILTER_JINC,
        "SINC" => Imagick::FILTER_SINC,
        "SINCFAST" => Imagick::FILTER_SINCFAST,
        "KAISER" => Imagick::FILTER_KAISER,
        "WELSH" => Imagick::FILTER_WELSH,
        "PARZEN" => Imagick::FILTER_PARZEN,
        "BOHMAN" => Imagick::FILTER_BOHMAN,
        "BARTLETT" => Imagick::FILTER_BARTLETT,
        "LAGRANGE" => Imagick::FILTER_LAGRANGE,
        "LANCZOS" => Imagick::FILTER_LANCZOS,
        "LANCZOSSHARP" => Imagick::FILTER_LANCZOSSHARP,
        "LANCZOS2" => Imagick::FILTER_LANCZOS2,
        "LANCZOS2SHARP" => Imagick::FILTER_LANCZOS2SHARP,
        "ROBIDOUX" => Imagick::FILTER_ROBIDOUX,
        "ROBIDOUXSHARP" => Imagick::FILTER_ROBIDOUXSHARP,
        "COSINE" => Imagick::FILTER_COSINE,
        "SPLINE" => Imagick::FILTER_SPLINE,
        "LANCZOSRADIUS" => Imagick::FILTER_LANCZOSRADIUS,
    ];
    
    echo '<html><body>';
    
    echo '<style>
        body {
            font-size: 12px;
            font-family: sans;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .blocks {
            display: flex;
            gap: 10px;
        }
    </style>';
    
    foreach ($qualities as $quality) {
        echo '<div class="blocks">';
        
        echo "<label>Quality: $quality</label>";
        foreach ($filters as $name => $filter) {
            $t1 = microtime(true);
            $im = $imagick->clone();
            if ($filter === 'scaleImage') {
                $im->scaleImage($width, 0);
            } else if ($filter === 'adaptiveResizeImage') {
                $im->adaptiveResizeImage($width, 0);
            } else {
                $im->resizeImage($width, 0, $filter, 1);
            }
            $t2 = microtime(true);
            $spent = round(($t2 - $t1) * 1000);
            
            $im->setImageFormat('webp');
            $im->setImageCompressionQuality($quality);
            
            $blob = $im->getImageBlob();
            $size = round(mb_strlen($blob, '8bit') / 1024);
            echo '<div class="block">'
                .$name
                .'<br>'
                .' ('.$size.'kb)' 
                .'<br/><img src="data:image/' . $im->getImageFormat() . ';base64,' . base64_encode($blob) . '"/></div>';
            ob_flush();
        }
        
        echo '</div>';
    }
    
    echo '</body>';
});

$app->run();
