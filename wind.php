<?php
/*
 * wind.php
 *
 * Looks up weather forecast for select cities and tweets out peak wind
 * speed, direction and time if forecast wind speed exceeds a pre-set
 * threshold.
 *
 * If you copy this file to /var/www/html with a PHP-enabled web server,
 * this will work, but this is meant to be run as a daily cron like this:
 * 
 *    /usr/bin/php -f MYDIR/wind.php
 *
 * OAuth.php and TwitterOAuth.php are required.
 * Find OAuth.php at http://oauth.net/
 * Get TwitterOAuth.php from https://github.com/abraham/twitteroauth
 *
 * You also need API keys from Twitter and Wunderground.com
 * 
 *    http://dev.twitter.com/
 *    http://api.wunderground.com/api
 *
 * Note that Weather Underground only allows 500 API calls per month
 * from their free account. This means only 15 or 16 cities can be checked
 * daily before you run into their limit.
 * 
 * I realize this use of PHP as a shell script instead of something
 * running inside of a web server is inconventional, but I'm an OS kernel
 * developer, not a script writer. Nonetheless, converting this into perl
 * or python isn't be difficult and is left as an exercise for the student.
 * 
 * Copyright (c) 2015 by Richard Masoner http://www.cyclelicio.us/
 * Find tweets from this at http://www.twitter.com/cyclelicious
 * 
 * This code is released with the hope that a beginner might learn how to
 * use existing libraries to check the weather and send automated tweets, 
 * hence you have a license to do whatever you want with this code.
 * If you implement this for your state / region, please tweet me at
 * @Cyclelicious and I'll be glad to help spread the word.
 * 
 * The TwitterOAuth and the OAuth libraries I use both have * non-viral
 * MIT licenses attached to them.
 */


require_once 'TwitterOAuth.php';

// Twitter Keys -- keep secret
define("CONSUMER_KEY","SECRET");
define("CONSUMER_SECRET", "SECRET");
define("OAUTH_TOKEN", "MORE-SECRETS");
define("OAUTH_SECRET", "SECRET");

// Weather Underground API Key -- keep this secret
define("WUNDERGROUND_KEY","SECRET");

//----------------------------------------------------------------------
// stationInfo class to declare weather station object
// 
class stationInfo {
  public $station;      // WX station name (e.g. "SJC" or "pws:KCASANTA87"
  public $name; // text description of the wx station, e.g. "San Jose, CA"
  public $windThresh;   // threshhold to tweet out wind speed

  function __construct($stationName, $stationDesc, $windThresh)
  {
    $this->station = $stationName;
    $this->name = $stationDesc;
    $this->windThresh = $windThresh;
    return;
  }
}

//----------------------------------------------------------------------
//  f_get_content() -- A curl-y drop in replacement for file_get_contents()
//
function f_get_content($URL){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $URL);
  // set proxy if you need one
  //curl_setopt($ch, CURLOPT_PROXY, 'myproxy.example.com:80');
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

//----------------------------------------------------------------------
// windInit() -- Initialize weather station database.
//
// TODO:
//   This static list of stations is lame.
//   Move this into an external database and read values from that.
// 
function windInit()
{

  echo "windInit<br />\n";

  // West Coast stations

  $wxdb1 = new stationInfo(
      trim("SJC"),              // weather station
      trim("San Jose, CA"),     // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdb2 = new stationInfo(
      trim("zmw:94614.5.99999"),// weather station
      trim("Oakland, CA"),      // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdb3 = new stationInfo(
      trim("LAX"),              // weather station
      trim("Los Angeles, CA"),  // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdb4 = new stationInfo(
      trim("zmw:92101.1.99999"),// weather station
      trim("San Diego, CA"),    // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdb5 = new stationInfo(
      trim("93101.1.99999"),    // weather station
      trim("Santa Barbara, CA"),// location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdb6 = new stationInfo(
      trim("zmw:97201.1.99999"),// weather station
      trim("Portland, OR"),     // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdb7 = new stationInfo(
      trim("SEA"),              // weather station
      trim("Seattle, WA"),      // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdb8 = new stationInfo(
      trim("SFO"),              // weather station
      trim("San Francisco"),    // location
      20                        // tweet wind speed forecasts over this value
      );


  $wxdb9 = new stationInfo(
      trim("zmw:93901.1.99999"),// weather station
      trim("Salinas, CA"),      // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdba = new stationInfo(
      trim("zmw:95060.1.99999"),// weather station
      trim("Santa Cruz, CA"),   // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbb = new stationInfo(
      trim("zmw:94203.1.99999"),// weather station
      trim("Sacramento, CA"),   // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbc = new stationInfo(
      trim("zmw:93650.1.99999"),// weather station
      trim("Fresno, CA"),       // location
      20                        // tweet wind speed forecasts over this value
      );


  $wxdbd = new stationInfo(
      trim("zmw:93301.1.99999"),// weather station
      trim("Bakersfield, CA"),  // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbe = new stationInfo(
      trim("santa%20clarita"),  // weather station
      trim("Santa Clarita, CA"),// location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbf = new stationInfo(
      trim("zmw:92373.1.99999"),// weather station
      trim("Redlands, CA"),     // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbg = new stationInfo(
      trim("Riverside%20CA"),   // weather station
      trim("Riverside, CA"),    // location
      20                        // tweet wind speed forecasts over this value
      );


  $wxdbh = new stationInfo(
      trim("zmw:92602.1.99999"),// weather station
      trim("Irvine, CA"),       // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbi = new stationInfo(
      trim("zmw:95401.1.99999"),// weather station
      trim("Santa Rosa, CA"),   // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbj = new stationInfo(
      trim("zmw:96001.1.99999"),// weather station
      trim("Redding, CA"),      // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbk = new stationInfo(
      trim("zmw:99001.3.99999"),// weather station
      trim("Spokane, WA"),      // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbk = new stationInfo(
      trim("zmw:99001.3.99999"),// weather station
      trim("Spokane, WA"),      // location
      20                        // tweet wind speed forecasts over this value
      );

  // Arizona

  $wxdbl = new stationInfo(
      trim("TUCSON"),   // weather station
      trim("Tucson, AZ"),// location
      20                // tweet wind speed forecasts over this value
      );

  $wxdbm = new stationInfo(
      trim("zmw:85001.1.99999"),// weather station
      trim("Phoenix, AZ"),      // location
      20                        // tweet wind speed forecasts over this value
      );

  // Utah

  $wxdbn = new stationInfo(
      trim("zmw:84601.1.99999"),// weather station
      trim("Provo, UT"),        // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbo = new stationInfo(
      trim("zmw:84060.1.99999"),// weather station
      trim("Park City, UT"),    // location
      20                        // tweet wind speed forecasts over this value
      );

  // Mountain States

  $wxdbp = new stationInfo(
      trim("zmw:81301.1.99999"),// weather station
      trim("Durango, CO"),      // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbq = new stationInfo(
      trim("zmw:81001.1.99999"),// weather station
      trim("Pueblo, CO"),       // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbr = new stationInfo(
      trim("zmw:80901.1.99999"),        // weather station
      trim("Colorado Springs, CO"),     // location
      20                                // tweet wind speed forecasts over this value
      );

  $wxdbs = new stationInfo(
      trim("zmw:80202.1.99999"),// weather station
      trim("Denver, CO"),       // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbt = new stationInfo(
      trim("zmw:80301.1.99999"),// weather station
      trim("Boulder, CO"),      // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbu = new stationInfo(
      trim("zmw:80301.1.99999"),// weather station
      trim("Ft Collins, CO"),   // location
      20                        // tweet wind speed forecasts over this value
      );

  $wxdbv = new stationInfo(
      trim("albuquerque"),
      trim("Albuquerque, NM"),
      15
      );

  // Texas

  $wxdbw = new stationInfo(
      trim("zmw:79821.2.99999"),
      trim("El Paso, TX"),
      20
      );

  $wxdbx = new stationInfo(
      trim("zmw:78201.1.99999"),
      trim("San Antonio, TX"),
      20
      );

  $wxdby = new stationInfo(
      trim("zmw:78701.1.99999"),
      trim("Austin, TX"),
      20
      );

  $wxdbz = new stationInfo(
      trim("Plano%20TX"),
      trim("Plano, TX"),
      20
      );

  $wxdbA = new stationInfo(
      trim("Houston%20TX"),
      trim("Houston, TX"),
      20
      );

  $wxdbB = new stationInfo(
      trim("Benbrook%20TX"),
      trim("Benbrook, TX"),
      20
      );

  $wxdbC = new stationInfo(
      trim("Wichita%20Falls%20TX"),
      trim("Wichita Falls, TX"),
      20
      );

  // Oklahoma 

  $wxdbD = new stationInfo(
      trim("Oklahoma%20City%20OK"),
      trim("Oklahoma City, OK"),
      20
      );

  $wxdbE = new stationInfo(
      trim("Tulsa%20OK"),
      trim("Tulsa, OK"),
      20
      );

  // Midwest

  $wxdbF = new stationInfo(
      trim("Kansas%20City%20MO"),
      trim("Kansas City, MO"),
      20
      );
  $wxdbG = new stationInfo(
      trim("Omaha%20NE"),
      trim("Omaha, NE"),
      20
      );

  $wxdbH = new stationInfo(
      trim("Minneapolis%20MN"),
      trim("Minneapolis, MN"),
      20
      );

  $wxdbI = new stationInfo(
      trim("St%20Louis%20MO"),
      trim("St Louis, MO"),
      20
      );



  $wxdb = array(
      $wxdb1,$wxdb2,$wxdb3,$wxdb4,$wxdb5,$wxdb6,$wxdb7,$wxdb8,$wxdb9,$wxdba,
      $wxdbb,$wxdbc,$wxdbd,$wxdbe,$wxdbf,$wxdbg,$wxdbh,$wxdbi,$wxdbj,$wxdbk,
      $wxdbl,$wxdbm,$wxdbn,$wxdbo,$wxdbp,$wxdbq,$wxdbr,$wxdbs,$wxdbt,$wxdbu,
      $wxdbv,$wxdbw,$wxdbx,$wxdby,$wxdbz,$wxdbA,$wxdbB,$wxdbC,$wxdbD,$wxdbE,
      $wxdbF,$wxdbG,$wxdbH,$wxdbI,
      );

  //$wxdb = array($wxdbI,$wxdbH); // XXX for debug

  return $wxdb;
}
//----------------------------------------------------------------------
// post_to_twitter()
// 
//   Posts to Twitter using Abraham Williams' RESTful
//   OAuth Twitter library from http://abrah.am/
//   You need a Twitter API key to use this.
//
function post_to_twitter($tweet)
{
  $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
  $content = $connection->get('account/verify_credentials');

  $r = $connection->post('statuses/update', array('status' => $tweet));

  return;
}


/*
 * ListWind()
 * 
 * Get hourly wind forecast from Weather Underground.
 * If wind forecast to exceed 20 MPH, tweet it out.
 * 
 * You need Weather Underground API key available from
 * http://api.wunderground.com/api
 */
function ListWind($wxdb,$tweet)
{

  $wxStation = "SJC";
  foreach ($wxdb as $wx)
  {

    $wxStation = $wx->station;
    echo "Station " . $wxStation . " <br />\n";


    // get hourly forecast

    $wurl = "http://api.wunderground.com/api/" . WUNDERGROUND_KEY . "/hourly/q/" . $wxStation . ".xml";
    $response = f_get_content($wurl);
    $parsed_xml = simplexml_load_string($response);
    if ($parsed_xml === FALSE) {
      // die quietly
      echo "Weather hourly forecast failed for " . $wurl . " <br />\n";
      return 0;
    }
    $maxtime = 0;               // hour for forecast max wind speed for the day
    $maxwind = 0;               // max wind speed in MPH
    $maxcompass = "N";  // forecast wind direction at max wind speed

    foreach ($parsed_xml->hourly_forecast->forecast as $forecast) {
      $windspeed = intval($forecast->wspd->english);
      $ftime = intval($forecast->FCTTIME->hour);
      $compass = $forecast->wdir->dir;

      // don't look at wind speeds for oh dark thirty
      if ($ftime < 5)
        continue;
      if ($ftime > 21)
        continue;

      if ($windspeed > $maxwind) {
        $maxwind = $windspeed;
        $maxtime = $ftime;
        $maxcompass = $compass;
      }
    }
    echo "maxwind = " . $maxwind . ", hour = " . $maxtime . " from direction " . $maxcompass . " <br />\n";

    // If wind speed greater than threshold, tweet it out.
    // Also check the "always tweet" flag.
    if (($tweet) || ($maxwind > $wx->windThresh)) {
      if ($maxtime > 12) {
        $hour = ($maxtime - 12) . " PM";
      } else {
        $hour = $maxtime . " AM";
      }

      // Build the tweet with link to Wunderground forecast page
      $twitterdata = "Chasing PRs / KOMs in " . $wx->name . "? Winds will peak today " . $maxwind . " MPH from the " . $maxcompass . " at " . $hour . " http://www.wunderground.com/cgi-bin/findweather/hdfForecast?query=" . $wxStation . "&apiref=194c2e3c30242aca";
      echo "\n--------------------------------------\n";
      echo $twitterdata;
      echo "\n--------------------------------------\n";
      post_to_twitter($twitterdata);
      // don't flood twitter with these all at once
      sleep(60);

    } else {
      // Wunderground only allows 10 API calls per minute,
      // so throttle this if no post to twitter
      sleep(7);
    }
  }
  }

}


?>

<?php

// Main page
$debug = 0; // XXX

//$action = $_GET['action'];
//$action = strtolower(ereg_replace("[^A-Za-z0-9]", "", $action)); // strip non-alphanumeric
$action = "";

echo "WIND <br />\n";

$wxdb = windInit();

/* URL?action=blah, where blah is one of :
 *   "tweet": always post results to twitter, otherwise only tweet when wind
 *            exceeds threshold.
 *
 * TODO:
 *   - Instead of the static table of weather stations, put them in a database and do db lookup
 *   - Add actions for first time wx database initialization, add entries to wx database, etc.
 */

switch ($action) {
  case "tweet":
    ListWind($wxdb,1);
  break;
  default:
  ListWind($wxdb,0);
  break;
}
?>

