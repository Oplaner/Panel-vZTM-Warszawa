<?php

enum PrivilegeScope: string {
    case canEditTimetableOfDepot = "CAN_EDIT_TIMETABLE_OF_DEPOT";
    case canManageDriversOfDepot = "CAN_MANAGE_DRIVERS_OF_DEPOT";
    case canManageVehiclesOfDepot = "CAN_MANAGE_VEHICLES_OF_DEPOT";
    case canViewTimetableOfDepot = "CAN_VIEW_TIMETABLE_OF_DEPOT";
    case canViewAllTimetables = "CAN_VIEW_ALL_TIMETABLES";
    case canViewAllVehicleLists = "CAN_VIEW_ALL_VEHICLE_LISTS";
    case canViewVehiclesOfDepot = "CAN_VIEW_VEHICLES_OF_DEPOT";

    public function getDescription(): string {
        return match ($this) {
            self::canEditTimetableOfDepot => "Może edytować grafik zakładu",
            self::canManageDriversOfDepot => "Może zarządzać kierowcami zakładu",
            self::canManageVehiclesOfDepot => "Może zarządzać pojazdami zakładu",
            self::canViewTimetableOfDepot => "Może przeglądać grafik zakładu",
            self::canViewAllTimetables => "Może przeglądać grafiki wszystkich zakładów",
            self::canViewAllVehicleLists => "Może przeglądać pojazdy wszystkich zakładów",
            self::canViewVehiclesOfDepot => "Może przeglądać pojazdy zakładu",
            default => $this->value
        };
    }
}

?>