SELECT 'Creating application database and app user...' AS '';

SOURCE /srv/www/etc/activity-log.phpolar.org/database/init.d/create_app_db.sql;

SELECT 'Creating migrations ledger database and migrator user...' AS '';

SOURCE /srv/www/etc/activity-log.phpolar.org/database/init.d/create_migration_ledger.sql;

SELECT 'Activity Log application database initialization complete.' AS '';
