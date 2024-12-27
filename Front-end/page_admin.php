<?php
ob_start();
// Déclarations 'use' pour PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
?>
<div class="wrap">
    <h1>Prospects</h1><br><br>

    <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="page" value="fg_entrees">
    <input type="hidden" name="action" value="filter_prospects"> 
    <input type="hidden" name="action" value="reinitialiser">

    <!-- Champs de date -->
    <label for="date_debut">Start Date:</label>
    <input type="date" id="date_debut" name="date_debut"
           value="<?php 
                      echo isset($_GET['date_debut']) ? esc_attr($_GET['date_debut']) : ''; 
                    ?>">

    <label for="date_fin">End Date :</label>
    <input type="date" id="date_fin" name="date_fin"
           value="<?php 
                    echo isset($_GET['date_fin']) ? esc_attr($_GET['date_fin']) : ''; 
                    ?>">

    <!-- Boutons avec une action spécifique -->
    <button type="submit" class="filtrer" name="action" value="filter_prospects">Filtrer les entrées</button> 
    <button type="submit" class="supprimer" name="action" value="delete_prospects">
        Déplacer vers la corbeille
    </button>
    <button type="submit" class="exporter" name="action" value="export_excel">Exporter vers Excel</button><br>
    <button type="submit" class="reinitialiser" name="action" value="reinitialiser" onclick="resetFilters()">réinitialiser les filtres</button><br><br>
 
    <!-- Table des prospects -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><input type="checkbox" id="select_all"></th>
                <th>#</th>
                <th>Statut</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Produit</th>
                <th>Lieu de livraison</th>
                <th>gclid_field</th>
                <th>Date de création</th>
            </tr>
        </thead>
        <tbody>
        <?php 
            global $wpdb;
            $table_name = $wpdb->prefix . 'fg_entrees';
            $date_debut = isset($_GET['date_debut']) ? sanitize_text_field($_GET['date_debut']) : '';
            $date_fin = isset($_GET['date_fin']) ? sanitize_text_field($_GET['date_fin']) : '';

                $query = "SELECT * FROM $table_name WHERE 1=1";

                if ($date_debut) {
                   $query .= $wpdb->prepare(" AND created_at >= %s", $date_debut . ' 00:00:00');
                }
                if ($date_fin) {
                   $query .= $wpdb->prepare(" AND created_at <= %s", $date_fin . ' 23:59:59');
                }

            $query .= " ORDER BY created_at DESC LIMIT 200";
            $results = $wpdb->get_results($query);
            
            foreach ($results as $entrees) {
                echo "<tr class='ligne-prospect'>
                        <td><input type='checkbox' name='prospects_ids[]' value='" . esc_attr($entrees->id) . "'></td>
                        <td>{$entrees->id}
                            <div class='options' style='display: none;'>
                              <a href='" . admin_url("admin-post.php?action=delete_prospect_single&id={$entrees->id}") . "' class='action-supprimer'>Supprimer<span> /</span></a> 
                              <a href='" . admin_url("admin-post.php?action=export_prospect_single&id={$entrees->id}") . "' class='action-exporter'>Exporter</a>
                            </div>
                        </td>
                        <td>{$entrees->statut}</td>
                        <td>{$entrees->nom}</td>
                        <td>{$entrees->telephone}</td>
                        <td>{$entrees->email}</td>
                        <td>{$entrees->produit}</td>
                        <td>{$entrees->livraison}</td>
                        <td>{$entrees->gclid}</td>
                        <td>{$entrees->created_at}</td>
                     </tr>";
            }
        ?>
        </tbody>
    </table>
</form>

<!-- message d'érreur si date fin<date-debut ou pas de données dans une plage de date -->
<?php if (isset($error_message)) : ?>
    <div class="notice notice-error">
        <p><?php echo esc_html($error_message); ?></p>
    </div>
<?php endif; ?>

<!-- messahe d'érreur si aucune donnée à exporter -->
<?php if (isset($_GET['error']) && $_GET['error'] === 'no_data') : ?>
    <div class="notice notice-error is-dismissible">
        <p>Aucun prospect à exporter.</p>
    </div>
<?php endif; ?>


</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const deleteButton = document.querySelector('button[name="action"][value="delete_prospects"]');

    if (deleteButton) {
        deleteButton.addEventListener('click', function(event) {
            // Sélectionner les cases cochées
            const selectedCheckboxes = document.querySelectorAll('input[name="prospects_ids[]"]:checked');

            // Si aucune case n'est cochée
            if (selectedCheckboxes.length === 0) {
                event.preventDefault(); // Empêcher la soumission du formulaire
                alert('Veuillez sélectionner au moins un prospect à supprimer.');
                return; // Arrêter l'exécution du reste du code
            }
            else{
                 // Si au moins une case est cochée, demander confirmation
            const confirmation = confirm('Êtes-vous sûr de vouloir supprimer les prospects sélectionnés ?');
            if (!confirmation) {
                event.preventDefault(); // Annuler la suppression si l'utilisateur clique sur "Annuler"
            }
            }
        });
    }
});

function resetFilters() {
        // Supprime les paramètres de l'URL
        const url = new URL(window.location.href);
        url.searchParams.delete('date_debut');
        url.searchParams.delete('date_fin');

        // Recharge la page avec l'URL nettoyée
        window.location.href = url.toString();

    }
</script>
<style>
    .supprimer{
        margin-left: 13px;
        font-size: 14px;
        /* background-color:#CE0000; */
        color: white;
        height: 35px;
        border-radius: 8px;
        border: none;
        background-color:#b32d2e;

    }
    .filtrer{
        margin-left: 13px;
        font-size: 14px;
        /* background-color:#135e96; */
        color: white;
        height: 35px;
        border-radius: 8px;
        border: none;
        width: 160px;
        background-color:#2271B1 !important;
        transition:  0.3s ease !important; 
    }
    .filtrer:hover{
        background-color: #135e96 !important;
    }
    .exporter{
        margin-left: 13px;
        font-size: 14px;
        background-color:green;
        color: white;
        height: 35px;
        border-radius: 8px;
        border: none;
        width: 160px;
    }
    form .reinitialiser{
        /* margin-left: 20px !important; */
        font-size: 14px !important;
        background-color:white;
        border-radius:5px !important;
        border: 2px solid #2271B1;
        height: 32px !important;
        width: 140px !important;
        margin-left: 65px !important;
        margin-top: 15px;
        padding:5px;
        
    }
    /* Styles pour le survol */
    .ligne-prospect:hover .options {
    display: block !important;
    position: absolute !important;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    padding: 5px;
    z-index: 10;
    font-size: 13px !important;
   }
   .ligne-prospect .options .action-supprimer{
    color: #b32d2e !important;
     border: none;
   }
   .ligne-prospect .options .action-exporter{
     color: #2c3338 !important;
     border: none;
   }
   .ligne-prospect .options .action-supprimer span{
    color:black;
   }


</style>

