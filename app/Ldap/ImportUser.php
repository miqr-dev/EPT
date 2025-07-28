<?php

namespace App\Ldap;

use App\Models\User;
use App\Models\City;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class ImportUser
{
  public function __invoke(LdapUser $ldapUser, User $eloquentUser)
  {
    $dn = $ldapUser->getDn();

    preg_match_all('/OU=([^,]+)/i', $dn, $ouMatches);
    $ous = $ouMatches[1] ?? [];

    $role = null;
    $cityId = null;

    // ----- Updated Role logic -----
    foreach ($ous as $ou) {
      if (stripos($ou, 'EDV') !== false) {
        $role = 'Admin';
        break;
      }
    }
    if (!$role) {
      foreach ($ous as $ou) {
        if (
          stripos($ou, 'Verwaltung') !== false ||
          stripos($ou, 'Mitarbeiter') !== false
        ) {
          $role = 'teacher';
          break;
        }
      }
    }
    // If still not set, assign participant
    if (!$role) {
      $role = 'participant';
    }

    // City logic as before
    $cities = City::all();
    foreach ($ous as $ou) {
      foreach ($cities as $city) {
        if (stripos($ou, $city->name) !== false) {
          $cityId = $city->id;
          break 2;
        }
      }
    }

    // Default city_id to 1 if not found
    if ($cityId === null) {
      $cityId = 1;
    }

    // Assign your other fields
    $eloquentUser->name      = $ldapUser->getFirstAttribute('cn');
    $eloquentUser->username  = $ldapUser->getFirstAttribute('samaccountname');
    $eloquentUser->email     = $ldapUser->getFirstAttribute('mail');
    $eloquentUser->firstname = $ldapUser->getFirstAttribute('givenName');
    $eloquentUser->role      = $role;
    $eloquentUser->city_id   = $cityId;

    $eloquentUser->save();
  }
}