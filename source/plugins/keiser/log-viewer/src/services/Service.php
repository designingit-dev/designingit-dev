<?php

namespace keiser\logviewer\services;

use craft\base\Component;

class Service extends Component
{

    public function getLogs()
    {
        $logsDirectory = CRAFT_STORAGE_PATH . 'runtime/logs';

        $logs = array_diff(scandir($logsDirectory), array('..', '.'));

        foreach($logs as $i => $log) {
            $path = $logsDirectory . '/' . $log;

            $logs[$i] = [
                'filename' => $log,
                'path' => $path,
                'size' => filesize($path),
                'mod' => filemtime($path)
            ];
        }

        return $logs;
    }


    public function readLogFile(Array $file)
    {
        $file['type'] = 'Craft';
        if (strpos($file['filename'], 'phperrors.log') > -1) {
            $file['type'] = 'PHP';
        }

        $file['data'] = $this->parseLog($file);

        $file['html'] = $this->generateTable($file['data'], $file['type']);

        return $file;
    }

    private function parseLog($file)
    {
        // Default parse pattern for Craft log files
        $pattern = '/^(\d{4}\/\d{2}\/\d{2}).*(\d{2}:\d{2}:\d{2}).*\[(.*?)\].*\[(.*?)\]/';

        // is this a php error log file?
        if ($file['type'] == 'PHP') {
            $pattern = '/^\[(.*?)\]/';
        }

        $contents = file_get_contents($file['path']);
        $lines = explode(PHP_EOL, $contents);

        // $lines = array_reverse($lines);

        $parsedLog = [];
        for($line = 0; $line < count($lines); $line++) {
            end($parsedLog);
            $lastParsedKey = key($parsedLog);
            $current_line = trim($lines[$line]);

            if(strlen($current_line) == 0 || preg_match('/^\**\*$/', $current_line)) {
                continue;
            }

            // get line headers
            preg_match($pattern, $current_line, $meta);

            if (count($meta)) {

                if ($file['type'] == 'PHP') {
                    list($header, $timestamp) = $meta;

                    $lineData = trim(str_replace($header, '', $current_line));

                    list($date, $time, $timezone) = explode(' ', $timestamp);

                    $parsedLog[] = [
                        'date' => $date,
                        'time' => $time,
                        'data' => $lineData
                    ];
                } else {
                    list($header, $date, $time, $status, $type) = $meta;

                    $lineData = trim(str_replace($header, '', $current_line));

                    $parsedLog[] = [
                        'date' => $date,
                        'time' => $time,
                        'status' => $status,
                        'type' => $type,
                        'data' => $lineData
                    ];
                }
            } else {
                $parsedLog[$lastParsedKey]['data'] = $parsedLog[$lastParsedKey]['data'] . PHP_EOL . $current_line;
            }
        }

        return $parsedLog;
    }

    private function generateTable($data, $type)
    {


        $html = '<table class="data fullwidth"><thead>';
        foreach (array_keys($data[0]) as $key) {
            $html .= '<th>'.ucfirst($key).'</th>';
        }
        $html .= '</thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $item) {
                $html .= '<td>'.nl2br($item).'</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}