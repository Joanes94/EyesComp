<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Réinitialise le cache des rôles et permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Définition des permissions par domaine
        $permissions = [
            // Boutiques & Organisation
            'stores.view', 'stores.create', 'stores.update', 'stores.delete',

            // Utilisateurs & Rôles
            'users.view', 'users.create', 'users.update', 'users.delete',

            // Catalogue Produits
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.manage',

            // Stock
            'stock.view', 'stock.adjust', 'stock.inventory', 'stock.transfer',

            // Caisse & Ventes (POS)
            'pos.access', 'sales.create', 'sales.view', 'sales.cancel', 'sales.discount',
            'cash_session.open_close',

            // Clients & Dettes
            'customers.view', 'customers.create', 'customers.update',
            'debts.view', 'debts.settle',

            // Fournisseurs & Achats
            'suppliers.manage', 'purchases.create', 'purchases.view',

            // Dépenses & Trésorerie
            'expenses.create', 'expenses.view', 'treasury.view',

            // Rapports & Finances
            'reports.sales', 'reports.financial', 'reports.cost_and_margin',

            // Journal d'audit & Paramètres
            'audit.view', 'settings.manage', 'subscription.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2. Rôle Super-Admin (plateforme)
        $superAdmin = Role::findOrCreate('super-admin', 'web');

        // 3. Rôle Propriétaire (accès complet entreprise)
        $owner = Role::findOrCreate('owner', 'web');
        $owner->syncPermissions(Permission::all());

        // 4. Rôle Gérant (gestion boutique, stocks, employés, rapports quotidiens sans abonnement)
        $manager = Role::findOrCreate('manager', 'web');
        $manager->syncPermissions([
            'stores.view', 'stores.update',
            'users.view', 'users.create', 'users.update',
            'products.view', 'products.create', 'products.update',
            'categories.manage',
            'stock.view', 'stock.adjust', 'stock.inventory', 'stock.transfer',
            'pos.access', 'sales.create', 'sales.view', 'sales.cancel', 'sales.discount',
            'cash_session.open_close',
            'customers.view', 'customers.create', 'customers.update',
            'debts.view', 'debts.settle',
            'suppliers.manage', 'purchases.create', 'purchases.view',
            'expenses.create', 'expenses.view', 'treasury.view',
            'reports.sales', 'reports.cost_and_margin',
            'audit.view',
        ]);

        // 5. Rôle Caissier / Vendeur (caisse rapide, encaissement, stock consultatif, dettes sans accès marges/coûts)
        $cashier = Role::findOrCreate('cashier', 'web');
        $cashier->syncPermissions([
            'products.view',
            'stock.view',
            'pos.access', 'sales.create', 'sales.view',
            'cash_session.open_close',
            'customers.view', 'customers.create',
            'debts.view', 'debts.settle',
        ]);

        // 6. Rôle Magasinier (réceptions, inventaire, mouvements sans accès caisse)
        $stockKeeper = Role::findOrCreate('stock_keeper', 'web');
        $stockKeeper->syncPermissions([
            'products.view',
            'stock.view', 'stock.adjust', 'stock.inventory', 'stock.transfer',
            'suppliers.manage', 'purchases.create', 'purchases.view',
        ]);
    }
}
