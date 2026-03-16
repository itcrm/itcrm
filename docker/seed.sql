-- Test user for automated tests (dev only)
-- Password: testpass
INSERT INTO `Users` (`Login`, `Password`, `Color`, `Name`, `Phone`, `AddDate`, `Status`)
VALUES ('testadmin', MD5('testpass'), '#000000', 'Test Admin', '', NOW(), 99);

-- Seed fixtures required by the Data-row tests (IDType=1, IDOrder=1, IDPerson=1)
INSERT INTO `Types` (`Code`, `Description`, `AddDate`, `Status`)
VALUES ('TEST', 'Test Type', NOW(), 1);

-- Saved filter for APPLY_SAVED_FILTER test (data_saved_filter state)
-- Note='model-test-note' so applying the filter pre-fills the Note field and matches the test data row.
INSERT INTO `Filters` (`Name`, `Note`, `AddDate`, `Status`)
VALUES ('MODEL-TEST-FILTER', 'model-test-note', NOW(), 1);

-- Changes column holds PHP-serialized diff history; non-empty means the changes button is visible in the UI.
INSERT INTO `Orders` (`IDUser`, `Code`, `Description`, `Color`, `AddDate`, `Status`, `Changes`)
VALUES (1, 'TEST-ORDER', 'Test Order', '#000000', NOW(), 1,
  'a:1:{s:19:"2024-01-01 12:00:00";a:1:{s:11:"Description";a:2:{s:3:"old";s:10:"Test Order";s:3:"new";s:13:"Test Order v2";}}}');

-- Reminder row for NAVIGATE_REMINDER test (data_reminder_view state).
-- RemindTo=1 (testadmin), past RemindDate triggers the red alert indicator.
-- Multiple fields set to enable all FilterForm field filter tests.
INSERT INTO `Data` (`IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `BookNote`, `TextOrder`, `TextType`, `PriceNote`, `Date`, `AddDate`,
                    `RemindDate`, `RemindTo`, `Status`)
VALUES (1, 1, 1, 1, 'model-reminder-note', 'model-place', 'model-place-done',
        '42.00', '5.00', '100.00', 'model-book-note', 'model-order-text', 'model-type-text', 'model-price-note',
        NOW(), NOW(), '2024-01-01 12:00:00', 1, 1);
