DROP TABLE IF EXISTS x2_media;
/*&*/
CREATE TABLE x2_portfolio_to_media(
    id              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    portfolio_id 	INT NOT NUL,
    media_id     	INT NOT NUL,
    private         TINYINT DEFAULT 0,
    private_end_date     DATE,
    listing_id      INT NOT NUL,
    buyer_id		INT NOT NUL,
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`,    title,    visible,    menuPosition,    searchable,    editable,    adminOnly,    custom,    toggleable)
VALUES
('portfolioMedia', 'PortfolioMedia', 1, 17, 0, 0, 0, 0, 0);
/*&*/
INSERT INTO x2_fields
(`modelName`, `fieldName`, `attributeLabel`, `modified`, `custom`, `type`, `required`, `uniqueConstraint`, `safe`, `readOnly`, `linkType`, `searchable`, `relevance`, `isVirtual`, `defaultValue`, `keyType`)
VALUES
('PortfolioMedia', 'id', 'ID', '0', '0', 'int', '0', '0', '1', '1', NULL, '0', '', '0', NULL, 'PRI'),
('PortfolioMedia', 'portfolio_id', 'Portfolio ID', '0', '0', 'int', '0', '0', '1', '1', NULL, '0', '', '0', NULL, ''),
('PortfolioMedia', 'media_id', 'Media ID', '0', '0', 'int', '0', '0', '1', '1', NULL, '0', '', '0', NULL, ''),
('PortfolioMedia', 'private', 'Private', '0', '0', 'int', '0', '0', '1', '1', NULL, '0', '', '0', NULL, ''),
('PortfolioMedia', 'private_end_date', 'Private date', '0', '0', 'date', '0', '0', '1', '1', NULL, '0', '', '0', NULL, ''),
('PortfolioMedia', 'listing_id', 'Listing ID', '0', '0', 'int', '0', '0', '1', '1', NULL, '0', '', '0', NULL, ''),
('PortfolioMedia', 'buyer_id', 'Buyer ID', '0', '0', 'int', '0', '0', '1', '1', NULL, '0', '', '0', NULL, '');
/*&*/

