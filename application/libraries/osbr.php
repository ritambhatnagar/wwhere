<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class OSBR {

    private $agent = "";
    private $info = array();

    function __construct() {
        $this->agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : NULL;
        $this->getBrowser();
        $this->getOS();
    }

    function getOS() {
//        $OS = array("Windows"   =>   "/Windows/i",
//                    "Linux"     =>   "/Linux/i",
//                    "Unix"      =>   "/Unix/i",
//                    "Mac"       =>   "/Mac/i"
//                    );
        $OS = array(
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        foreach ($OS as $key => $value) {
            if (preg_match($key, $this->agent)) {
                $this->info = array_merge($this->info, array("Operating System" => $value));
                break;
            }
        }
        return $this->info['Operating System'];
    }

    function getBrowser() {
        $browser = array("Navigator" => "/Navigator(.*)/i",
            "Firefox" => "/Firefox(.*)/i",
            "Internet Explorer" => "/MSIE(.*)/i",
            "Google Chrome" => "/chrome(.*)/i",
            "MAXTHON" => "/MAXTHON(.*)/i",
            "Opera" => "/OPR(.*)/i",
            "Safari" => "/Safari(.*)/i",
            "Netscape" => "/Netscape(.*)/i",
            "Konqueror" => "/Konqueror(.*)/i",
            "Handheld Browser" => "/mobile(.*)/i",
            "Apple Browser" => "/AppleWebKit(.*)/i"
        );

        foreach ($browser as $key => $value) {
            if (preg_match($value, $this->agent)) {
                $this->info = array_merge($this->info, array("Browser" => $key));
                $this->info = array_merge($this->info, array(
                    "Version" => $this->getVersion($key, $value, $this->agent)));
                break;
            } else {
                $this->info = array_merge($this->info, array("Browser" => "UnKnown"));
                $this->info = array_merge($this->info, array("Version" => "UnKnown"));
            }
        }
        return $this->info['Browser'];
    }

    function getVersion($browser, $search, $string) {
        $browser = $this->info['Browser'];
        $version = "";
        $browser = strtolower($browser);
        preg_match_all($search, $string, $match);
        
        switch ($browser) {
            case "firefox": $version = str_replace("/", "", $match[1][0]);
                break;

            case "internet explorer": $version = substr($match[1][0], 0, 4);
                break;

            case "opr": $version = str_replace("/", "", substr($match[1][0], 0, 5));
                break;

            case "navigator": $version = substr($match[1][0], 1, 7);
                break;

            case "maxthon": $version = str_replace(")", "", $match[1][0]);
                break;

            case "safari": $version = str_replace("/", "", $match[1][0]);
                break;
            case "netscape": $version = str_replace("/", "", $match[1][0]);
                break;
            case "konqueror": $version = str_replace("/", "", $match[1][0]);
                break;
            case "handheld browser": $version = str_replace("/", "", $match[1][0]);
                break;
            case "apple browser": $version = str_replace("/", "", $match[1][0]);
                break;
            case "google chrome": $version = substr($match[1][0],1,10);
        }
        return $version;
    }

    function showInfo($switch) {
        $switch = strtolower($switch);
        switch ($switch) {
            case "browser": return $this->info['Browser'];
                break;

            case "os": return $this->info['Operating System'];
                break;

            case "version": return $this->info['Version'];
                break;

            case "all" : return array($this->info['Operating System'], $this->info['Browser'], $this->info["Version"], $this->agent);
                break;

            default: return "Unkonw";
                break;
        }
    }

}
