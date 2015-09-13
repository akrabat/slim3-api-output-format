<?php
namespace App;

use Slim\Http\Body;

class Renderer
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function render($response, array $data)
    {
        $contentType = $this->determineContentType($this->request->getHeaderLine('Accept'));
        switch ($contentType) {
            case 'text/html':
                $output = $this->renderHtml($data);
                break;

            case 'application/json':
            default:
                $contentType = 'application/json';
                $output = json_encode($data);
                break;


        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response
                ->withHeader('Content-type', $contentType)
                ->withBody($body);
    }

    /**
     * Render Array as HTML (thanks to joind.in's -api project!)
     *
     * This code is cribbed from https://github.com/joindin/joindin-api/blob/master/src/views/HtmlView.php
     *
     * @return string
     */
    private function renderHtml($data)
    {
        $html =  <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>API v2</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
    body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 14px;
        color: #000;
        padding: 5px;
    }

    ul {
        padding-bottom: 15px;
        padding-left: 20px;
    }
    a {
        color: #2368AF;
    }
    </style>
</head>
<body>
HTML;
        $html .= $this->arrayToHtml($data);

        $html .= <<<HTML
</body>
</html>
HTML;
    }


    /**
     * Recursively render an array to an HTML list
     *
     * @param array $content data to be rendered
     *
     * @return null
     */
    private function arrayToHtml(array $content, $html = '')
    {
        echo "<ul>\n";

        // field name
        foreach ($content as $field => $value) {
            echo "<li><strong>" . $field . ":</strong> ";
            if (is_array($value)) {
                // recurse
                $this->arrayToHtml($value);
            } else {
                // value, with hyperlinked hyperlinks
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $value = htmlentities($value, ENT_COMPAT, 'UTF-8');
                if ((strpos($value, 'http://') === 0) || (strpos($value, 'https://') === 0)) {
                    echo "<a href=\"" . $value . "\">" . $value . "</a>";
                } else {
                    echo $value;
                }
            }
            echo "</li>\n";
        }
        echo "</ul>\n";
    }

    /**
     * Read the accept header and determine which content type we know about
     * is wanted.
     *
     * @param  string $acceptHeader Accept header from request
     * @return string
     */
    private function determineContentType($acceptHeader)
    {
        $list = explode(',', $acceptHeader);
        $known = ['application/json', 'application/xml', 'text/html'];
        
        foreach ($list as $type) {
            if (in_array($type, $known)) {
                return $type;
            }
        }

        return 'text/html';
    }
}
