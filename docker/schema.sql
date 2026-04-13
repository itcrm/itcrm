-- CreateTable
CREATE TABLE `Data` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `IDDoc` VARCHAR(250) NOT NULL DEFAULT '',
    `IDUser` INTEGER NOT NULL DEFAULT 0,
    `IDOrder` INTEGER NOT NULL DEFAULT 0,
    `TextOrder` VARCHAR(250) NOT NULL DEFAULT '',
    `IDType` INTEGER NOT NULL DEFAULT 0,
    `TextType` VARCHAR(250) NOT NULL DEFAULT '',
    `Sum` FLOAT NOT NULL DEFAULT 0.00,
    `Hours` FLOAT NOT NULL DEFAULT 0.00,
    `PlaceTaken` VARCHAR(400) NOT NULL DEFAULT '',
    `PlaceDone` VARCHAR(400) NOT NULL DEFAULT '',
    `IDPerson` INTEGER NOT NULL DEFAULT 0,
    `Note` TEXT NOT NULL DEFAULT '',
    `Date` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `BookNote` TEXT NOT NULL DEFAULT '',
    `TotalPrice` FLOAT NOT NULL DEFAULT 0.00,
    `PriceNote` VARCHAR(250) NOT NULL DEFAULT '',
    `AddDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `RemindDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `RemindDateEnd` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `RemindTo` INTEGER NOT NULL DEFAULT 0,
    `Status` INTEGER NOT NULL DEFAULT 0,
    `Hidden` INTEGER NOT NULL DEFAULT 0,
    `Changes` TEXT NOT NULL DEFAULT '',
    `AdminEdit` INTEGER NOT NULL DEFAULT 0,
    `allDay` INTEGER NOT NULL DEFAULT 0,
    `Noma` INTEGER NOT NULL DEFAULT 0,
    `Color` VARCHAR(32) NOT NULL DEFAULT '',
    `NoteText` TEXT NOT NULL DEFAULT ''
);
CREATE INDEX `Data_AddDate` ON `Data`(`AddDate`);
CREATE INDEX `Data_Date` ON `Data`(`Date`);
CREATE INDEX `Data_IDDoc` ON `Data`(`IDDoc`);
CREATE INDEX `Data_IDOrder` ON `Data`(`IDOrder`);
CREATE INDEX `Data_IDPerson` ON `Data`(`IDPerson`);
CREATE INDEX `Data_IDUser` ON `Data`(`IDUser`);
CREATE INDEX `Data_TextOrder` ON `Data`(`TextOrder`);
CREATE INDEX `Data_TextType` ON `Data`(`TextType`);
CREATE INDEX `Data_Type` ON `Data`(`IDType`);

-- CreateTable
CREATE TABLE `Error` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `Time` TEXT NOT NULL,
    `User` INTEGER NOT NULL,
    `Type` INTEGER NOT NULL,
    `Url` VARCHAR(255) NOT NULL,
    `Line` VARCHAR(50) NOT NULL,
    `Message` VARCHAR(255) NOT NULL
);

-- CreateTable
CREATE TABLE `Filters` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `Name` VARCHAR(50) NOT NULL DEFAULT '',
    `Date` INTEGER NOT NULL DEFAULT 0,
    `DateType` INTEGER NOT NULL DEFAULT 0,
    `IDPerson` VARCHAR(255) NOT NULL DEFAULT '0',
    `IDOperator` VARCHAR(255) NOT NULL DEFAULT '0',
    `IDOrder` VARCHAR(255) NOT NULL DEFAULT '0',
    `TextOrder` VARCHAR(50) NOT NULL DEFAULT '',
    `IDType` VARCHAR(255) NOT NULL DEFAULT '0',
    `TextType` VARCHAR(50) NOT NULL DEFAULT '',
    `Sum` FLOAT NOT NULL DEFAULT 0,
    `Hours` FLOAT NOT NULL DEFAULT 0,
    `PlaceTaken` VARCHAR(250) NOT NULL DEFAULT '',
    `PlaceDone` VARCHAR(250) NOT NULL DEFAULT '',
    `Note` VARCHAR(255) NOT NULL DEFAULT '',
    `BookNote` VARCHAR(255) NOT NULL DEFAULT '',
    `AddDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `Status` INTEGER NOT NULL DEFAULT 0,
    `Search` VARCHAR(255) NOT NULL DEFAULT ''
);

-- CreateTable
CREATE TABLE `Info` (
    `IDData` INTEGER NOT NULL DEFAULT 0,
    `IDSupplier` INTEGER NOT NULL DEFAULT 0,
    `IDUser` INTEGER NOT NULL DEFAULT 0,
    `Info` VARCHAR(250) NOT NULL DEFAULT '',
    `Color` VARCHAR(7) NOT NULL DEFAULT '',
    `AddDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`IDData`, `IDSupplier`)
);

-- CreateTable
CREATE TABLE `Orders` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `IDUser` INTEGER NOT NULL DEFAULT 0,
    `Code` VARCHAR(50) NOT NULL DEFAULT '',
    `Description` VARCHAR(200) NOT NULL DEFAULT '',
    `Color` VARCHAR(20) NOT NULL DEFAULT '',
    `AddDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `Status` INTEGER NOT NULL DEFAULT 0,
    `Changes` TEXT NOT NULL DEFAULT ''
);
CREATE UNIQUE INDEX `Orders_Code` ON `Orders`(`Code`);

-- CreateTable
CREATE TABLE `Suppliers` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `IDUser` INTEGER NOT NULL DEFAULT 0,
    `Name` VARCHAR(30) NOT NULL DEFAULT '',
    `Description` VARCHAR(200) NOT NULL DEFAULT '',
    `Color` VARCHAR(7) NOT NULL DEFAULT '',
    `AddDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `Status` INTEGER NOT NULL DEFAULT 0
);

-- CreateTable
CREATE TABLE `Types` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `Code` VARCHAR(20) NOT NULL DEFAULT '',
    `Description` VARCHAR(200) NOT NULL DEFAULT '',
    `AddDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `Status` INTEGER NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX `Types_Code` ON `Types`(`Code`);

-- CreateTable
CREATE TABLE `Users` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `Login` VARCHAR(32) NOT NULL DEFAULT '',
    `Password` VARCHAR(32) NOT NULL DEFAULT '',
    `Color` VARCHAR(20) NOT NULL DEFAULT '',
    `Name` VARCHAR(100) NOT NULL DEFAULT '',
    `Phone` VARCHAR(100) NOT NULL DEFAULT '',
    `AddDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `Status` INTEGER NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX `Users_Login` ON `Users`(`Login`);

-- CreateTable
CREATE TABLE `audit` (
    `audit_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `audit_time` INTEGER NULL,
    `audit_uid` INTEGER NULL DEFAULT -1,
    `audit_event` TEXT NULL,
    `audit_ip` VARCHAR(255) NULL,
    `audit_severity` INTEGER NULL DEFAULT 0,
    `audit_item` INTEGER NULL,
    `audit_facility` INTEGER NULL,
    `audit_subitem` INTEGER NULL DEFAULT 0,
    `audit_backtrace` TEXT NULL,
    `audit_classname` VARCHAR(255) NULL,
    `audit_instance` TEXT NULL,
    `audit_url` VARCHAR(255) NULL
);
CREATE INDEX `audit_idx_classname` ON `audit`(`audit_classname`);
CREATE INDEX `audit_idx_item` ON `audit`(`audit_item`);
CREATE UNIQUE INDEX `audit_idx_time_id` ON `audit`(`audit_time`, `audit_id`);

-- CreateTable
CREATE TABLE `categories_linear` (
    `id` INTEGER NOT NULL PRIMARY KEY,
    `level` INTEGER NULL,
    `parent` INTEGER NULL,
    `iorder` INTEGER NULL,
    `title` VARCHAR(255) NULL
);

-- CreateTable
CREATE TABLE `data_auditing` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `ID_Row` INTEGER NOT NULL,
    `IDDoc` VARCHAR(250) NOT NULL DEFAULT '',
    `IDUser` INTEGER NOT NULL DEFAULT 0,
    `IDOrder` INTEGER NOT NULL DEFAULT 0,
    `TextOrder` VARCHAR(250) NOT NULL DEFAULT '',
    `IDType` INTEGER NOT NULL DEFAULT 0,
    `TextType` VARCHAR(250) NOT NULL DEFAULT '',
    `Sum` FLOAT NOT NULL DEFAULT 0.00,
    `Hours` FLOAT NOT NULL DEFAULT 0.00,
    `PlaceTaken` VARCHAR(400) NOT NULL DEFAULT '',
    `PlaceDone` VARCHAR(400) NOT NULL DEFAULT '',
    `IDPerson` INTEGER NOT NULL DEFAULT 0,
    `Note` TEXT NOT NULL,
    `Date` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `BookNote` TEXT NOT NULL,
    `TotalPrice` FLOAT NOT NULL DEFAULT 0.00,
    `PriceNote` VARCHAR(250) NOT NULL DEFAULT '',
    `AddDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `RemindDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `RemindDateEnd` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `RemindTo` INTEGER NOT NULL DEFAULT 0,
    `Status` INTEGER NOT NULL DEFAULT 0,
    `Hidden` INTEGER NOT NULL DEFAULT 0,
    `AdminEdit` INTEGER NOT NULL DEFAULT 0,
    `allDay` INTEGER NOT NULL,
    `Noma` INTEGER NOT NULL DEFAULT 0,
    `Color` VARCHAR(32) NOT NULL,
    `NoteText` TEXT NOT NULL
);
CREATE INDEX `data_auditing_AddDate` ON `data_auditing`(`AddDate`);
CREATE INDEX `data_auditing_Date` ON `data_auditing`(`Date`);
CREATE INDEX `data_auditing_IDDoc` ON `data_auditing`(`IDDoc`);
CREATE INDEX `data_auditing_IDOrder` ON `data_auditing`(`IDOrder`);
CREATE INDEX `data_auditing_IDPerson` ON `data_auditing`(`IDPerson`);
CREATE INDEX `data_auditing_IDUser` ON `data_auditing`(`IDUser`);
CREATE INDEX `data_auditing_TextOrder` ON `data_auditing`(`TextOrder`);
CREATE INDEX `data_auditing_TextType` ON `data_auditing`(`TextType`);
CREATE INDEX `data_auditing_Type` ON `data_auditing`(`IDType`);

-- CreateTable
CREATE TABLE `fileindex` (
    `idx_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `path` VARCHAR(255) NULL,
    `filename` VARCHAR(255) NULL,
    `type` INTEGER NULL,
    `size` INTEGER NULL,
    `date` TEXT NULL,
    `updateDate` INTEGER NULL
);
CREATE INDEX `fileindex_idx_full` ON `fileindex`(`filename`, `path`);

-- CreateTable
CREATE TABLE `groups_linear` (
    `id` INTEGER NOT NULL PRIMARY KEY,
    `level` INTEGER NULL,
    `parent` INTEGER NULL,
    `iorder` INTEGER NULL,
    `title` VARCHAR(255) NULL
);

-- CreateTable
CREATE TABLE `warehouse` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `rindasID` INTEGER NOT NULL,
    `partID` INTEGER NOT NULL,
    `daudzums` FLOAT NOT NULL DEFAULT 0.00,
    `type` INTEGER NOT NULL DEFAULT 0,
    `Shop` INTEGER NOT NULL DEFAULT 0,
    `ShopCategoryID` VARCHAR(255) NOT NULL,
    `ShopDescription` VARCHAR(255) NOT NULL,
    `ShopModelID` VARCHAR(255) NOT NULL,
    `ShopTitle` VARCHAR(255) NOT NULL,
    `OriginalCode` VARCHAR(255) NOT NULL,
    `addition` INTEGER NOT NULL DEFAULT 100,
    `offer` INTEGER NOT NULL DEFAULT 0,
    `state` INTEGER NOT NULL DEFAULT 0,
    `used` INTEGER NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX `warehouse_rindasID` ON `warehouse`(`rindasID`);

-- CreateTable
CREATE TABLE `noma` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `RowID` INTEGER NOT NULL,
    `PersonID` INTEGER NOT NULL DEFAULT 0,
    `AutoID` INTEGER NOT NULL DEFAULT 0,
    `DayMony` INTEGER NOT NULL,
    `From` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `To` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
    `Price` INTEGER NOT NULL DEFAULT 0,
    `CautionMoney` INTEGER NOT NULL DEFAULT 0,
    `Summ` INTEGER NOT NULL DEFAULT 0,
    `GetLocation` VARCHAR(255) NOT NULL DEFAULT '0',
    `ReturnLocation` VARCHAR(255) NOT NULL DEFAULT '0',
    `Nr` VARCHAR(255) NOT NULL DEFAULT '0',
    `ligums` TEXT NOT NULL,
    `Akts` TEXT NOT NULL,
    `Pielikums` TEXT NOT NULL
);

-- CreateTable
CREATE TABLE `nomasauto` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `Nosaukums` VARCHAR(255) NOT NULL DEFAULT '',
    `Reg_nr` VARCHAR(255) NOT NULL DEFAULT '',
    `Sasija` VARCHAR(255) NOT NULL DEFAULT '',
    `Reg_ap` VARCHAR(255) NOT NULL DEFAULT '',
    `Vertiba` FLOAT NOT NULL DEFAULT 0.00,
    `OrderID` INTEGER NOT NULL DEFAULT 0,
    `Status` INTEGER NOT NULL DEFAULT 0
);

-- CreateTable
CREATE TABLE `parameters` (
    `param_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `param_name` VARCHAR(255) NOT NULL DEFAULT '',
    `param_value` TEXT NULL
);
CREATE UNIQUE INDEX `parameters_idx_param_name` ON `parameters`(`param_name`);

-- CreateTable
CREATE TABLE `photo_tagger` (
    `ID` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `photoid` INTEGER NOT NULL,
    `y` FLOAT NOT NULL DEFAULT 0.0,
    `width` FLOAT NOT NULL DEFAULT 0.0,
    `height` FLOAT NOT NULL DEFAULT 0.0,
    `message` TEXT NOT NULL,
    `x` VARCHAR(20) NOT NULL
);

-- CreateTable
CREATE TABLE `session_storage` (
    `session_id` VARCHAR(60) NOT NULL DEFAULT '0',
    `session_param` VARCHAR(255) NOT NULL DEFAULT '',
    `session_value` TEXT NOT NULL,
    PRIMARY KEY (`session_id`, `session_param`)
);
CREATE INDEX `session_storage_session_param` ON `session_storage`(`session_param`);

-- CreateTable
CREATE TABLE `telegram_users` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `users_id` INTEGER NOT NULL,
    `users_login` VARCHAR(20) NOT NULL,
    `tg_user` VARCHAR(100) NOT NULL,
    `tg_chat_id` INTEGER NOT NULL,
    `pas_nr` VARCHAR(45) NULL
);
CREATE UNIQUE INDEX `telegram_users_tg_chat_id_UNIQUE` ON `telegram_users`(`tg_chat_id`);

-- CreateTable
CREATE TABLE `thumbcache` (
    `thumb_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `path` VARCHAR(255) NULL,
    `width` INTEGER NULL,
    `height` INTEGER NULL,
    `filesize` INTEGER NULL,
    `filedate` INTEGER NULL
);

-- Trigger: log old row into data_auditing on every Data update
CREATE TRIGGER data_Trigger AFTER UPDATE ON Data
FOR EACH ROW
BEGIN
    INSERT INTO data_auditing
        (ID_Row, IDDoc, IDUser, IDOrder, TextOrder, IDType, TextType,
         Sum, Hours, PlaceTaken, PlaceDone, IDPerson, Note, Date,
         BookNote, TotalPrice, PriceNote, AddDate, RemindDate,
         RemindDateEnd, RemindTo, Status, Hidden, AdminEdit, allDay, Noma, Color, NoteText)
    VALUES
        (OLD.ID, OLD.IDDoc, OLD.IDUser, OLD.IDOrder, OLD.TextOrder,
         OLD.IDType, OLD.TextType, OLD.Sum, OLD.Hours, OLD.PlaceTaken,
         OLD.PlaceDone, OLD.IDPerson, OLD.Note, OLD.Date, OLD.BookNote,
         OLD.TotalPrice, OLD.PriceNote, OLD.AddDate, OLD.RemindDate,
         OLD.RemindDateEnd, OLD.RemindTo, OLD.Status, OLD.Hidden,
         OLD.AdminEdit, OLD.allDay, OLD.Noma, OLD.Color, OLD.NoteText);
END;
