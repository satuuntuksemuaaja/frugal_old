<?php
namespace vl\core;

use Carbon\Carbon;
use Config;
use Exception;
use Google_Client as GoogleClient;
use Google_Service_Calendar as GoogleCalendar;
use Google_Service_Calendar_Event as GoogleEvent;
use Google_Service_Calendar_EventDateTime as GoogleEventDateTime;
use Illuminate\Support\Facades\Log;
use Input;
use User;

class Google
{

    static public function getClient($user = null)
    {
        if ($user && $user->google)
        {
            $token = $user->google;
        }
        else $token = null;
        $client = new GoogleClient();
        $client->setApplicationName('Frugal Kitchens Ops');
        // Visit https://code.google.com/apis/console?api=plus to generate your
        // client id, client secret, and to register your redirect uri.
        $client->setClientId(Config::get('google.GOOGLE_CLIENT_ID'));
        $client->setClientSecret(Config::get('google.GOOGLE_SECRET'));
        if ($user && $user->id)
        {
            $client->setRedirectUri(sprintf(Config::get('google.GOOGLE_REDIRECT_URI'), $user->id));
        }
        $client->setDeveloperKey(Config::get('google.GOOGLE_DEV_KEY'));
        $client->setScopes(['http://www.google.com/calendar/feeds']);
        $client->setAccessType('offline');
        try
        {
            if ($token)
            {
                $client->setAccessToken($token);
                if ($client->isAccessTokenExpired())
                {
                    $token = json_decode($user->google, true); //Get the token stored, and convert JSON to array
                    $client->refreshToken($token['refresh_token']); //Set the refresh token
                    $newtoken = $client->getAccessToken(); //Call the getAccessToken() function to get a new access token for you
                    $user->google = $newtoken;
                    $user->save();
                }
            }
        } catch (Exception $e)
        {
            Log::info("Google Exception: ". $e->getMessage());
        }

        return $client;
    }

    /**
     * Creates a google event on the user calendar.
     *
     * @param  User $user [description]
     * @param  [type] $params array: title, location, description, start, end
     * @return bool [type]         [description]
     */
    static public function event($user, $params)
    {
        // Debug Testing:
        // $user = User::find(1);

        // Need title, location, start (int) end,
        if (!$user) return true;
        if (!$user->google) return true;

        $client = self::getClient($user);
        $gCal = new GoogleCalendar($client);
        try
        {
            //$client->setUseObjects(true);
            $event = new GoogleEvent();
            $event->setSummary($params['title']);
            $event->setLocation($params['location']);
            $event->setDescription($params['description']);
            $start = new GoogleEventDateTime();
            //$startConv = date("Y-m-d", $params['start']) . "T" . date("H:i:s", $params['start']) . ".000-05:00";
            $startConv = Carbon::parse($params['start'])->format('Y-m-d\TH:i:s\.\0\0\0\-\0\4\:\0\0');
            $start->setDateTime($startConv);
            $event->setStart($start);
            $end = new GoogleEventDateTime();
            //$endConv = date("Y-m-d", $params['end']) . "T" . date("H:i:s", $params['end']) . ".000-05:00";
            $endConv = Carbon::parse($params['end'])->format('Y-m-d\TH:i:s\.\0\0\0\-\0\4\:\0\0');
            $end->setDateTime($endConv);
            $event->setEnd($end);
            \Log::info("Built Event for $startConv to $endConv");
            $createdEvent = $gCal->events->insert('primary', $event);
            if ($createdEvent)
            {
                return $createdEvent->getId();
            }
            else return false;
        } catch (\Exception $e)
        {

        }
    }

    static public function authenticateUser(User $user)
    {
        $client = self::getClient($user);
        return $client->createAuthUrl();
    }

    static public function setAuthToken(User $user)
    {
        $client = self::getClient($user);
        $client->authenticate(Input::get('code'));
        $token = $client->getAccessToken();
        $user->google = $token;
        $user->save();
  }

}