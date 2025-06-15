<?php

final class AccessChecker {
    public static function userCanAccess(Access $access, array $pathData): bool {
        global $_USER;
        $isAuthenticated = isset($_USER);
        return match ($access->group) {
            AccessGroup::guestsOnly => !$isAuthenticated,
            AccessGroup::oneOfProfiles => $isAuthenticated && self::profileMatchExists($_USER, $access, $pathData),
            AccessGroup::anyProfile => $isAuthenticated,
            AccessGroup::everyone => true
        };
    }

    private static function profileMatchExists(User $user, Access $access, array $pathData): bool {
        foreach ($user->getActiveProfiles() as $userProfile) {
            $matchExists = match (get_class($userProfile)) {
                DirectorProfile::class => self::directorMatchExists($access),
                PersonnelProfile::class => self::personnelMatchExists($userProfile, $access, $pathData),
                DriverProfile::class => self::driverMatchExists($user, $access, $pathData)
            };

            if ($matchExists) {
                return true;
            }
        }

        return false;
    }

    private static function directorMatchExists(Access $access): bool {
        foreach ($access->profiles as $accessProfile) {
            if ($accessProfile == DirectorProfile::class) {
                return true;
            }
        }

        return false;
    }

    private static function personnelMatchExists(PersonnelProfile $userProfile, Access $access, array $pathData): bool {
        foreach ($access->profiles as $accessProfile) {
            if ($accessProfile != PersonnelProfile::class) {
                continue;
            }

            if (is_null($access->allowedPersonnelPrivileges)) {
                return true;
            }

            $matchExists = self::privilegeMatchExists(
                $userProfile->getPrivileges(),
                $access->allowedPersonnelPrivileges,
                $pathData
            );

            if ($matchExists) {
                return true;
            }
        }

        return false;
    }

    private static function privilegeMatchExists(array $userPrivileges, array $accessPrivileges, array $pathData): bool {
        foreach ($userPrivileges as $userPrivilege) {
            foreach ($accessPrivileges as ["scope" => $scope, "entityKey" => $entityKey]) {
                if ($userPrivilege->getScope() != $scope) {
                    continue;
                }

                if (is_null($userPrivilege->getAssociatedEntityID()) || is_null($entityKey)) {
                    return true;
                }

                if ($userPrivilege->getAssociatedEntityID() == $pathData[$entityKey]) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function driverMatchExists(User $driver, Access $access, array $pathData): bool {
        foreach ($access->profiles as $accessProfile) {
            if ($accessProfile != DriverProfile::class) {
                continue;
            }

            if (is_null($access->carrierKey)) {
                return true;
            }

            foreach ($driver->getActiveContracts() as $contract) {
                if ($contract->getCarrier()->getID() == $pathData[$access->carrierKey]) {
                    return true;
                }
            }
        }

        return false;
    }
}

?>