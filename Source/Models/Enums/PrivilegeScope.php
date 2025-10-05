<?php

enum PrivilegeScope: string {
    case canEditTimetableOfDepot = "CAN_EDIT_TIMETABLE_OF_DEPOT";
    case canManageDriversOfDepot = "CAN_MANAGE_DRIVERS_OF_DEPOT";
    case canManageVehiclesOfDepot = "CAN_MANAGE_VEHICLES_OF_DEPOT";
    case canViewTimetableOfDepot = "CAN_VIEW_TIMETABLE_OF_DEPOT";
    case canViewAllTimetables = "CAN_VIEW_ALL_TIMETABLES";
    case canViewAllVehicleLists = "CAN_VIEW_ALL_VEHICLE_LISTS";
    case canViewVehiclesOfDepot = "CAN_VIEW_VEHICLES_OF_DEPOT";
}

?>