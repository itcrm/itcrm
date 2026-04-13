-- =============================================================================
-- Seed data for development and testing
-- All names, codes, addresses, and other identifiers are fictional.
-- Domain: a flower shop / floral services business.
-- =============================================================================

-- ─── Users ──────────────────────────────────────────────────────────────────────
-- Admin user for automated tests (dev only)
-- Password: Alice123
INSERT INTO `Users` (`Login`, `Password`, `Color`, `Name`, `Phone`, `AddDate`, `Status`)
VALUES ('Alice', '8714bd74bdd83c760184dd89c829f3a2', '#000000', 'Alice Morgan', '+1 555-0100', datetime('now'), 99);

-- Additional staff users
-- Passwords: John123, Sarah123, Mark123, Emma123, disabled123 (all MD5-hashed)
INSERT INTO `Users` (`Login`, `Password`, `Color`, `Name`, `Phone`, `AddDate`, `Status`)
VALUES
  ('John',     'a5391e96f8d48a62e8c85381df108e98', '#1E88E5', 'John Smith',     '+1 555-0101', '2023-06-15 09:00:00', 1),
  ('Sarah',    '4eb641b60073572fcd1afe9017ac52db', '#E53935', 'Sarah Jones',    '+1 555-0102', '2023-07-01 10:30:00', 1),
  ('Mark',     'b38ac18016d255ee4e9a364fb6490ebf', '#43A047', 'Mark Davis',     '+1 555-0103', '2023-09-10 08:00:00', 1),
  ('Emma',     'dda71ccb468d687e9d9b39e55bb20733', '#FB8C00', 'Emma Wilson',    '+1 555-0104', '2024-01-20 14:00:00', 1),
  ('disabled', '024dda788e21e6bae8d736f807919fdd', '#9E9E9E', 'Old Account',    '',            '2022-01-01 00:00:00', 0);

-- ─── Types (service/work categories) ────────────────────────────────────────────
-- Seed fixtures required by the Data-row tests (IDType=1, IDOrder=1, IDPerson=1)
INSERT INTO `Types` (`Code`, `Description`, `AddDate`, `Status`)
VALUES ('BOUQUET', 'Bouquet arrangement', datetime('now'), 1);

INSERT INTO `Types` (`Code`, `Description`, `AddDate`, `Status`)
VALUES
  ('ARRANGE',   'Floral arrangement',       '2023-01-10 09:00:00', 1),
  ('DELIVERY',  'Delivery',                 '2023-01-10 09:00:00', 1),
  ('SETUP',     'Event setup',              '2023-01-10 09:00:00', 1),
  ('MAINT',     'Plant maintenance',        '2023-01-10 09:00:00', 1),
  ('CONSULT',   'Consultation',             '2023-01-10 09:00:00', 1),
  ('INSPECT',   'Quality inspection',       '2023-02-15 10:00:00', 1),
  ('WARRANTY',  'Replacement / warranty',   '2023-02-15 10:00:00', 1),
  ('INACTIVE',  'Inactive type',            '2022-01-01 00:00:00', 0);

-- ─── Orders (projects / work orders) ────────────────────────────────────────────
-- Changes column holds PHP-serialized diff history; non-empty means the changes button is visible in the UI.
INSERT INTO `Orders` (`IDUser`, `Code`, `Description`, `Color`, `AddDate`, `Status`, `Changes`)
VALUES (1, 'SPRING-GALA', 'Spring Gala', '#000000', datetime('now'), 1,
  'a:1:{s:19:"2024-01-01 12:00:00";a:1:{s:11:"Description";a:2:{s:3:"old";s:11:"Spring Gala";s:3:"new";s:14:"Spring Gala v2";}}}');

INSERT INTO `Orders` (`IDUser`, `Code`, `Description`, `Color`, `AddDate`, `Status`, `Changes`)
VALUES
  (2, 'GREENLEAF-2024',  'Greenleaf Corp – weekly office flowers',    '#1E88E5', '2023-06-20 09:00:00', 1, ''),
  (2, 'SHOP-MAINT',      'Shop equipment maintenance',                '#E53935', '2023-08-01 10:00:00', 1, ''),
  (3, 'RENTAL-2024',     'Vehicle rental 2024',                       '#43A047', '2024-01-05 08:00:00', 1, ''),
  (4, 'WEDDING-PARKER',  'Parker-Adams wedding flowers',              '#FB8C00', '2024-02-10 11:00:00', 1, ''),
  (3, 'WARRANTY-COOL',   'Cooler warranty service',                   '#8E24AA', '2023-03-15 09:00:00', 1, ''),
  (2, 'HOTEL-MARLIN',    'Hotel Marlin lobby arrangements',           '#00897B', '2023-11-01 08:30:00', 1, ''),
  (5, 'ARCHIVE-2022',    'Archive 2022 – old project',                '#9E9E9E', '2022-03-01 00:00:00', 0, '');

-- ─── Suppliers (vendors / partners) ─────────────────────────────────────────────
INSERT INTO `Suppliers` (`IDUser`, `Name`, `Description`, `Color`, `AddDate`, `Status`)
VALUES
  (2, 'Valley Blooms Wholesale',  'Fresh flower wholesaler',              '#1565C0', '2023-02-01 09:00:00', 1),
  (2, 'GreenThumb Supplies',      'Floral foam, tape, and tools',         '#C62828', '2023-03-15 10:00:00', 1),
  (3, 'Vase & Vessel Co',         'Vases, pots, and containers',          '#2E7D32', '2023-04-20 11:00:00', 1),
  (4, 'RibbonCraft',              'Ribbons, wrapping, and packaging',     '#EF6C00', '2023-06-01 14:00:00', 1),
  (2, 'CoolTech Refrigeration',   'Walk-in cooler parts and service',     '#6A1B9A', '2023-01-10 08:00:00', 1),
  (3, 'Old Supplier Co',          'No longer in use',                     '#757575', '2021-06-01 08:00:00', 0);

-- ─── Filters (saved search templates) ───────────────────────────────────────────
INSERT INTO `Filters` (`Name`, `Note`, `AddDate`, `Status`)
VALUES ('Weekly flowers', 'Weekly office flower delivery', datetime('now'), 1);

INSERT INTO `Filters` (`Name`, `IDOrder`, `IDType`, `Note`, `AddDate`, `Status`)
VALUES
  ('Greenleaf maintenance',  '2', '5', '',                               '2024-01-15 09:00:00', 1),
  ('Deliveries today',       '0', '3', '',                               '2024-01-20 10:00:00', 1),
  ('Cooler warranty',        '6', '8', '',                               '2024-02-01 11:00:00', 1);

-- ─── Data (core work records) ───────────────────────────────────────────────────
-- Today-dated rows: seeded with datetime('now') so they appear under Today/Week/Month/Year filters.
-- Each row uses different Users/Orders/Types to allow select-filter discrimination.

-- Row 1: Gala centerpiece — Alice, SPRING-GALA, BOUQUET. All fields populated for filter coverage.
-- RemindTo=1 (Alice), past RemindDate triggers the red alert indicator (NAVIGATE_REMINDER test).
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `BookNote`, `TextOrder`, `TextType`, `PriceNote`,
                    `Date`, `AddDate`, `RemindDate`, `RemindTo`, `Status`)
VALUES ('gala-centerpiece', 1, 1, 1, 1, 'Spring gala centerpiece arrangement', 'Walk-in cooler', 'Grand Ballroom, Riverside Hotel',
        85.00, 3.0, 145.00, 'Priority order, client VIP', 'Centerpiece with peonies and roses', 'Hand-tied garden bouquet', 'Loyalty discount 10%',
        datetime('now'), datetime('now'), '2024-01-01 12:00:00', 1, 1);

-- Row 2: Gala client meeting — Alice, SPRING-GALA, BOUQUET. Light fields for doc-filter testing.
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `BookNote`, `TextOrder`, `TextType`, `PriceNote`,
                    `Date`, `AddDate`, `Status`)
VALUES ('gala-client-meeting', 1, 1, 1, 1, 'Meeting with client about arrangement', 'Consultation room', 'In-store',
        0.00, 1.5, 120.00, 'Initial consultation booked', 'Gala flower selection', 'Consultation', 'Consultation fee waived',
        datetime('now'), datetime('now'), 1);

-- Row 3: Greenleaf Monday delivery — John, GREENLEAF-2024, DELIVERY.
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `BookNote`, `TextOrder`, `TextType`, `PriceNote`,
                    `Date`, `AddDate`, `Status`)
VALUES ('greenleaf-monday', 2, 2, 3, 2, 'Weekly office flower delivery', 'Walk-in cooler', 'Greenleaf Corp, 100 Oak Ave',
        250.00, 1.5, 290.00, 'Standing Monday delivery', 'Seasonal mixed bouquets', 'Delivery run', 'Net 14 days',
        datetime('now'), datetime('now'), 1);

-- Row 4: Parker bridal consultation — Sarah, WEDDING-PARKER, CONSULT.
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `BookNote`, `TextOrder`, `TextType`, `PriceNote`,
                    `Date`, `AddDate`, `Status`)
VALUES ('parker-bridal-consult', 3, 5, 6, 3, 'Bridal bouquet consultation with Linda Parker', 'Consultation room', 'Parker residence, 22 River Rd',
        0.00, 2.0, 160.00, 'Bridal package review', 'Wedding flower options', 'Wedding consultation', 'Fee waived with booking',
        datetime('now'), datetime('now'), 1);

-- Row 5: Cancelled arrangement — soft-deleted (Status=-1) so FIND_DELETED_ROWS and SEARCH_WITH_DELETED can assert on it.
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `Date`, `AddDate`, `Status`)
VALUES ('cancelled-arrangement', 1, 1, 2, 1, 'Cancelled arrangement for lobby display', 'Walk-in cooler', 'Grand Ballroom, Riverside Hotel',
        0.00, 0.0, 0.00, datetime('now'), datetime('now'), -1);

-- Floral arrangements
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `BookNote`, `TextOrder`, `TextType`, `PriceNote`,
                    `Date`, `AddDate`, `Status`)
VALUES
  ('greenleaf-lobby-refresh', 2, 2, 2, 2, 'Lobby arrangement refresh – 12 mixed bouquets', 'Walk-in cooler', 'Greenleaf Corp, 100 Oak Ave',
   180.00, 2.5, 242.50, 'Weekly office flower rotation', 'GREENLEAF-2024', 'ARRANGE', 'No tax',
   '2024-03-15 10:00:00', '2024-03-14 08:00:00', 1),

  ('cooler-compressor-repair', 3, 3, 2, 3, 'Cooler compressor repair and thermostat recalibration', 'Shop back room', 'On-site',
   85.00, 1.0, 115.00, 'Compressor seal + recalibration', 'SHOP-MAINT', 'ARRANGE', '',
   '2024-03-18 14:00:00', '2024-03-17 09:00:00', 1),

  ('cooler-panel-warranty', 2, 6, 8, 2, 'Replacement cooler panel (warranty)', 'CoolTech warehouse', 'On-site',
   0.00, 1.5, 0.00, 'Warranty claim #CT-2024-889', 'WARRANTY-COOL', 'WARRANTY', 'Warranty – no charge',
   '2024-04-02 09:00:00', '2024-04-01 08:00:00', 1);

-- Deliveries
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `BookNote`, `TextOrder`, `TextType`, `PriceNote`,
                    `Date`, `AddDate`, `Status`)
VALUES
  ('greenleaf-bouquet-delivery', 4, 2, 3, 4, 'Monday morning delivery – 5 reception bouquets', 'Walk-in cooler', 'Greenleaf Corp, 100 Oak Ave',
   250.00, 1.0, 290.00, '5x premium mixed bouquets', 'GREENLEAF-2024', 'DELIVERY', 'Tax included, delivery included',
   '2024-03-20 11:00:00', '2024-03-19 10:00:00', 1),

  ('parker-wedding-delivery', 2, 5, 3, 2, 'Bridal bouquet and 8 bridesmaid bouquets delivery', 'Walk-in cooler', 'Riverside Chapel, 22 River Rd',
   1200.00, 2.0, 1280.00, 'Bridal + 8 bridesmaid bouquets', 'WEDDING-PARKER', 'DELIVERY', 'Tax 10%',
   '2024-04-10 08:00:00', '2024-04-08 16:00:00', 1);

-- Event setups
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `BookNote`, `TextOrder`, `TextType`, `PriceNote`,
                    `Date`, `AddDate`, `Status`)
VALUES
  ('parker-venue-setup', 4, 5, 4, 4, 'Wedding venue setup – arch, centerpieces, pew flowers', 'Walk-in cooler', 'Riverside Chapel, 22 River Rd',
   0.00, 8.0, 480.00, 'Arch + 15 centerpieces + 20 pew arrangements', 'WEDDING-PARKER', 'SETUP', '60 USD/h',
   '2024-04-15 08:00:00', '2024-04-14 09:00:00', 1),

  ('marlin-lobby-install', 3, 7, 4, 3, 'Hotel lobby display installation and rotation', 'Walk-in cooler', 'Hotel Marlin, 5 Harbor Blvd',
   350.00, 4.0, 590.00, 'Large lobby vase + 4 side table arrangements', 'HOTEL-MARLIN', 'SETUP', '',
   '2024-05-02 09:00:00', '2024-04-30 11:00:00', 1);

-- Maintenance / consultation visits
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `BookNote`, `TextOrder`, `TextType`, `PriceNote`,
                    `Date`, `AddDate`, `RemindDate`, `RemindTo`, `Status`)
VALUES
  ('greenleaf-plant-care-apr', 2, 2, 5, 2, 'Monthly plant care visit – April', '', 'Greenleaf Corp, 100 Oak Ave',
   0.00, 3.0, 180.00, 'Watering, pruning, replacements', 'GREENLEAF-2024', 'MAINT', '60 USD/h',
   '2024-04-25 09:00:00', '2024-04-24 08:00:00', '2024-05-25 09:00:00', 2, 1),

  ('greenleaf-seasonal-consult', 5, 2, 6, 5, 'Consultation on seasonal flower subscription', '', 'Greenleaf Corp, 100 Oak Ave',
   0.00, 2.0, 160.00, 'Seasonal options review with client', 'GREENLEAF-2024', 'CONSULT', '80 USD/h',
   '2024-05-10 14:00:00', '2024-05-09 10:00:00', '0000-00-00 00:00:00', 0, 1),

  ('marlin-freshness-check', 3, 7, 5, 3, 'Lobby arrangement freshness check before inspection', '', 'Hotel Marlin, 5 Harbor Blvd',
   0.00, 1.5, 90.00, 'Pre-inspection quality check', 'HOTEL-MARLIN', 'MAINT', '60 USD/h',
   '2024-06-01 10:00:00', '2024-05-30 08:00:00', '0000-00-00 00:00:00', 0, 1);

-- Completed (Status=0) and hidden records for filter testing
INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `Date`, `AddDate`, `Status`)
VALUES
  ('archive-display-teardown', 2, 8, 2, 2, 'End-of-season display teardown and disposal', 'Client lobby', 'Shop',
   0.00, 4.0, 240.00, '2023-11-15 09:00:00', '2023-11-14 08:00:00', 0),

  ('archive-vase-return', 3, 8, 3, 3, 'Return rented vases to client', 'Shop', 'Client lobby',
   15.00, 0.5, 15.00, '2023-12-01 10:00:00', '2023-11-30 09:00:00', 0);

INSERT INTO `Data` (`IDDoc`, `IDUser`, `IDOrder`, `IDType`, `IDPerson`, `Note`, `PlaceTaken`, `PlaceDone`,
                    `Sum`, `Hours`, `TotalPrice`, `Date`, `AddDate`, `Status`, `Hidden`)
VALUES
  ('archive-hidden-error', 2, 8, 2, 2, 'Incorrectly entered record – hidden', '', '',
   0.00, 0.0, 0.00, '2023-10-01 00:00:00', '2023-10-01 00:00:00', 0, 1);

-- ─── Info (supplier notes on work items) ────────────────────────────────────────
-- IDData references Data.ID; IDSupplier references Suppliers.ID
INSERT INTO `Info` (`IDData`, `IDSupplier`, `IDUser`, `Info`, `Color`, `AddDate`)
VALUES
  (2, 1, 2, 'Roses ordered from Valley Blooms, ETA 2 days',       '#1565C0', '2024-03-14 08:30:00'),
  (2, 5, 2, 'CoolTech confirmed warranty claim',                   '#6A1B9A', '2024-03-14 09:00:00'),
  (3, 2, 3, 'Floral foam running low – reorder from GreenThumb',  '#C62828', '2024-03-17 10:00:00'),
  (5, 1, 4, 'Peonies received from Valley Blooms – 5 bunches',     '#1565C0', '2024-03-19 14:00:00'),
  (6, 3, 2, 'Tall glass vases backordered – Vase & Vessel Co',    '#2E7D32', '2024-04-09 08:00:00'),
  (8, 4, 3, 'Extra satin ribbon needed for hotel display',         '#EF6C00', '2024-04-29 16:00:00');

-- ─── Warehouse (parts inventory) ───────────────────────────────────────────────
INSERT INTO `warehouse` (`rindasID`, `partID`, `daudzums`, `type`, `Shop`, `ShopCategoryID`, `ShopDescription`, `ShopModelID`, `ShopTitle`, `OriginalCode`, `addition`, `offer`, `state`, `used`)
VALUES
  (1001, 1, 50, 1, 0, 'ROSE',    'Red roses, long stem 60cm',          'ROSE-RED-60',   'Red Rose 60cm',             'ROSE-RED-60',    3,  0, 1, 0),
  (1002, 2, 30, 1, 0, 'TULIP',   'Dutch tulips, mixed colors',         'TULIP-MIX',     'Mixed Tulips',              'TULIP-MIX',      2,  0, 1, 0),
  (1003, 3, 80, 1, 0, 'FOAM',    'Floral foam brick, standard',        'FOAM-STD',      'Floral Foam Brick',         'FOAM-STD',       1,  0, 1, 0),
  (1004, 4, 20, 1, 0, 'VASE',    'Glass cylinder vase 30cm',           'VASE-CYL-30',   'Cylinder Vase 30cm',        'VASE-CYL-30',    8,  0, 1, 0),
  (1005, 5, 15, 1, 0, 'VASE',    'Ceramic pot white 20cm',             'POT-WHT-20',    'White Ceramic Pot 20cm',    'POT-WHT-20',     6,  0, 1, 0),
  (1006, 6,  0, 1, 0, 'RIBBON',  'Satin ribbon ivory 25mm',            'RIB-IVR-25',    'Ivory Satin Ribbon 25mm',   'RIB-IVR-25',     2,  0, 0, 0),
  (1007, 7, 40, 1, 0, 'WRAP',    'Brown kraft wrapping paper roll',     'WRAP-KRAFT',    'Kraft Wrapping Paper',      'WRAP-KRAFT',      1, 1, 1, 0),
  (1008, 8, 10, 1, 0, 'LILY',    'White oriental lily, 3-stem bunch',  'LILY-WHT-3',    'White Oriental Lily',       'LILY-WHT-3',     5,  0, 1, 0),
  (1009, 9,  5, 1, 0, 'PEONY',   'Peony, blush pink',                  'PEONY-BLUSH',   'Blush Peony',               'PEONY-BLUSH',    7,  0, 1, 0),
  (1010, 10, 0, 1, 0, 'SUPPLY',  'Floral tape green 12mm',             'TAPE-GRN-12',   'Green Floral Tape 12mm',    'TAPE-GRN-12',    1,  0, 0, 1);

-- ─── Rental vehicles (nomasauto) ────────────────────────────────────────────────
INSERT INTO `nomasauto` (`Nosaukums`, `Reg_nr`, `Sasija`, `Reg_ap`, `Vertiba`, `OrderID`, `Status`)
VALUES
  ('Delivery Van A', 'VAN-001', 'VIN00000000000001', 'Springfield', 28000.00, 4, 1),
  ('Delivery Van B', 'VAN-002', 'VIN00000000000002', 'Springfield', 32000.00, 4, 1),
  ('Compact Van C',  'VAN-003', 'VIN00000000000003', 'Shelbyville', 15000.00, 4, 0);

-- ─── Rental agreements (noma) ───────────────────────────────────────────────────
INSERT INTO `noma` (`RowID`, `PersonID`, `AutoID`, `DayMony`, `From`, `To`, `Price`, `CautionMoney`, `Summ`,
                    `GetLocation`, `ReturnLocation`, `Nr`, `ligums`, `Akts`, `Pielikums`)
VALUES
  (5, 1, 1, 45, '2024-03-20 08:00:00', '2024-03-22 18:00:00', 135, 200, 335,
   'Shop, 12 Maple St', 'Greenleaf Corp, 100 Oak Ave', 'RENT-2024-001',
   'Van rental for bouquet delivery to Greenleaf Corp.', '', ''),

  (6, 2, 2, 55, '2024-04-10 07:00:00', '2024-04-12 19:00:00', 165, 250, 415,
   'Shop, 12 Maple St', 'Riverside Chapel, 22 River Rd', 'RENT-2024-002',
   'Van rental for Parker-Adams wedding flower delivery.', '', ''),

  (8, 3, 1, 45, '2024-05-02 08:00:00', '2024-05-02 18:00:00', 45, 200, 245,
   'Shop, 12 Maple St', 'Hotel Marlin, 5 Harbor Blvd', 'RENT-2024-003',
   'Van rental for hotel lobby arrangement delivery.', '', '');

-- ─── Categories and groups (hierarchical classification) ────────────────────────
INSERT INTO `categories_linear` (`id`, `level`, `parent`, `iorder`, `title`)
VALUES
  (1, 0, 0, 1, 'Fresh flowers'),
  (2, 1, 1, 1, 'Roses'),
  (3, 1, 1, 2, 'Tulips'),
  (4, 1, 1, 3, 'Lilies'),
  (5, 0, 0, 2, 'Arrangements'),
  (6, 1, 5, 1, 'Bouquets'),
  (7, 1, 5, 2, 'Centerpieces'),
  (8, 1, 5, 3, 'Wreaths'),
  (9, 0, 0, 3, 'Supplies'),
  (10, 1, 9, 1, 'Vases'),
  (11, 1, 9, 2, 'Foam and tape'),
  (12, 1, 9, 3, 'Wrapping');

INSERT INTO `groups_linear` (`id`, `level`, `parent`, `iorder`, `title`)
VALUES
  (1, 0, 0, 1, 'Corporate clients'),
  (2, 1, 1, 1, 'Offices'),
  (3, 1, 1, 2, 'Hotels and venues'),
  (4, 0, 0, 2, 'Event clients'),
  (5, 1, 4, 1, 'Weddings'),
  (6, 1, 4, 2, 'Parties'),
  (7, 0, 0, 3, 'Individual customers');

-- ─── Parameters (application configuration) ────────────────────────────────────
INSERT INTO `parameters` (`param_name`, `param_value`)
VALUES
  ('company_name',    'Bloom & Petal'),
  ('company_reg',     'C-000099'),
  ('company_address', '12 Maple St, Springfield, 62701'),
  ('company_bank',    'First Bank'),
  ('company_account', 'US00XXXX0000000099'),
  ('company_phone',   '+1 555-0100'),
  ('company_email',   'hello@example.com'),
  ('vat_rate',        '10'),
  ('currency',        'USD'),
  ('default_language','en');
