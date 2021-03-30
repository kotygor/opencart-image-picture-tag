<?php
class ControllerExtensionModulePicture extends Controller {
    private $formats = [ // mime-types
        'webp' =>   'image/webp',
        'jpg'  =>   'image/jpeg',
//        'png',
    ];
    private $width_set = [
        160,
        320,
        720,
        1200,
        4000 // max image size
    ];


    public function index($data) {
        return '';
    }

    public function create_srcset($data) {

        $img =
            ('' != $data['image']
                && file_exists(DIR_IMAGE . $data['image'])
            ) ?
                $data['image'] : 'placeholder.png';
        $width = $data['width'];
        $height = $data['height'];
        $alt = !empty($data['alt'])? $data['alt'] : "";
        $title = !empty($data['title'])? $data['title'] : "";
        $class = !empty($data['class'])? $data['class'] : "";
        $transform = 'w'; //$data['transform'];

        $scale = $height / $width;

        $info = pathinfo($img);
        $info['dirname'] = !empty($info['dirname'])? $info['dirname'] : '';
        $img_path = $info['dirname'] . '/' . $info['filename'];

        $this->load->model('tool/image');

        $srcset = [];

        foreach ($this->formats as $ext => $mime ) {
            if (!file_exists(DIR_IMAGE . $img_path . '.' . $ext)) {
                $src = new Image(DIR_IMAGE . $img);
                $src->save(DIR_IMAGE . $img_path . '.' . $ext);
            }
            foreach ($this->width_set as $item_width) {

                if($item_width < $width) {
                    $srcset['srcset'][$mime][$item_width . 'w'] = str_replace([HTTPS_SERVER, '//'], ['', '/'],
                        $this->model_tool_image->resize( // $filename, $width, $height, $type = ''
                            $img_path . '.' . $ext,
                            $item_width,
                            $item_width * $scale,
                            $transform
                        )
                    )
                    ;
                } else {
                    $srcset['srcset'][$mime][$width . 'w'] = str_replace([HTTPS_SERVER, '//'], ['', '/'],
                        $this->model_tool_image->resize( // $filename, $width, $height, $type = ''
                            $img_path . '.' . $ext,
                            $width,
                            $height,
                            $transform
                        )
                    );
                    $srcset['srcset'][$mime][(2* $width) . 'w'] = str_replace([HTTPS_SERVER, '//'], ['', '/'],
                        $this->model_tool_image->resize( // $filename, $width, $height, $type = ''
                            $img_path . '.' . $ext,
                            2 * $width,
                            2 * $height,
                            $transform
                        )
                    );
                    continue;
                }

            }
        }
        $srcset['src'] = str_replace([HTTPS_SERVER, '//'], ['', '/'], $this->model_tool_image->resize( // $filename, $width, $height, $type = ''
                            $img,
                            $width,
                            $height,
                            $transform
                        ));
        $srcset['alt']   = $alt;
        $srcset['title'] = $title;
        $srcset['class'] = $class;

//        echo "<pre>" . print_r(['picture' => $srcset], 1) . "</pre>"; die();
        return $this->load->view('extension/module/picture', ['picture' => $srcset]);
    }

}