<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/elementor-78/Frontend/style.css">
</head>
<body> 
    <div class="fg_plugin">
       <form id="fg_form"  method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
        <h4 id="titre_formulaire"><pre>Remplissez ce Formulaire et Recevez Vos Tarifs dans cinq 
    Minutes </pre></h4>  
        <input type="hidden" name="fg_delete_prospects_nonce_field" value="<?php echo wp_create_nonce('fg_delete_prospects_nonce'); ?>" />
        <select name="statut" id="statut" class="fg">
            <option value="" disabled selected>Statut</option>
            <option value="Proféssionnel">Proféssionnel</option>
            <option value="Particulier">Particulier</option>
        </select> 
        <span id="erreur_statut" class="erreur"></span>

        <input type="text" name="nom" id="nom" placeholder="Prénom et Nom" class="fg">

        <input type="number" name="telephone" id="telephone" placeholder="Téléphone" class="fg">

        <input type="text" name="email" placeholder="E-mail" id="email"  class="fg"  value="<?php echo esc_attr($_POST['email'] ?? ''); ?>">
       <span id="erreur_email" class=erreur></span>

        <select name="produit" id="produit" class="fg">
            <option value="" disabled selected>Produit</option>  
            <option value="Bungalow bureau (3m,6m,combinaison)">Bungalow bureau (3m,6m,combinaison)</option>
            <option value="Bungalow sanitaire(2.3m,3m,6m)">Bungalow sanitaire(2.3m,3m,6m)</option>     
            <option value="Studio jardin(6m)">Studio jardin(6m)</option>  
            <option value="Container de stockage(1.98m,2.43m,3m,4.55m)">Container de stockage(1.98m,2.43m,3m,4.55m)</option>   
            <option value="Container maritime(1.98m,2.43m,3m,6m),12m">Container maritime(1.98m,2.43m,3m,6m),12m</option> 
            <option value="Container frigorifique(6m,12m)">Container frigorifique(6m,12m)</option>  
            <option value="Container isotherme(6m,12m)">Container isotherme(6m,12m)</option> 
            <option value="Container spécifique(open top,open side)">Container spécifique(open top,open side)</option> 
        </select>
        <span id=erreur_produit class=erreur></span>

        <select name="livraison" id="livraison"  class="fglivraison">
           <option value="" disabled selected>Lieu de livraison</option>																						
            <option value="01-ain">01-ain</option>																						
	        <option value="02-aisne">02-aisne</option>	
            <option value="03-allier">03-allier</option>																								
            <option value="04-alpes-de-haute-provence">04-alpes-de-haute-provence</option>
            <option value=" 05-hautes-alpes	"> 05-hautes-alpes</option>	
            <option value="06-alpes-maritimes">06-alpes-maritimes</option>	
            <option value="07-ardeche">07-ardeche</option>	
            <option value="08-ardennes">08-ardennes</option>	
            <option value="09-ariege">09-ariege</option>	
            <option value="10-aube">10-aube</option>																								
            <option value="11-aude">11-aude</option>																								
            <option value="12-aveyron">12-aveyron</option>																								
            <option value="13-bouches-du-rhone">13-bouches-du-rhone</option>																								
            <option value="14-calvados">14-calvados</option>																								
            <option value="15-cantal">15-cantal</option>		
            <option value="16-charente	">16-charente</option>	
            <option value="17-charente-maritime">17-charente-maritime</option>	
            <option value="18-cher">18-cher</option>	
            <option value="19-correze">19-correze</option>	
            <option value="2a-corse-du-sud">2a-corse-du-sud</option>	
            <option value="2b-haute-corse">2b-haute-corse</option>	
            <option value="21-cote-d-or">21-cote-d-or</option>	
            <option value="22-cotes-d-armor">22-cotes-d-armor</option>	
            <option value="23-creuse">23-creuse</option>		
            <option value="24-dordogne">24-dordogne</option>		
            <option value="25-doubs">25-doubs</option>
            <option value="26-drome">26-drome</option>	
            <option value="27-eure">27-eure</option>
            <option value="28-eure-et-loir">28-eure-et-loir</option>	
            <option value="29-finistere">29-finistere</option>	
            <option value="30-gard">30-gard</option>	
            <option value="31-haute-garonne">31-haute-garonne</option>	
            <option value="32-gers">32-gers</option>	
            <option value="33-gironde">33-gironde</option>	
            <option value="34-herault">34-herault</option>	
            <option value="35-ille-et-vilaine">35-ille-et-vilaine</option>	
            <option value="36-indre">36-indre</option>	
            <option value="37-indre-et-loire">37-indre-et-loire</option>	
            <option value="38-isere">38-isere</option>	
            <option value="39-jura">39-jura</option>	
            <option value="40-landes">40-landes</option>	
            <option value="41-loir-et-cher">41-loir-et-cher</option>	
            <option value="42-loire">42-loire</option>		
            <option value="43-haute-loire">43-haute-loire</option>	
            <option value="44-loire-atlantique">44-loire-atlantique</option>		
            <option value="45-loiret">45-loiret</option>	
            <option value="46-lot">46-lot</option>	
            <option value="47-lot-et-garonne">47-lot-et-garonne</option>	
            <option value="48-lozere">48-lozere</option>	
            <option value="49-maine-et-loire">49-maine-et-loire</option>	
            <option value="50-manche">50-manche</option>	
            <option value="51-marne">51-marne</option>	
            <option value="52-haute-marne">52-haute-marne</option>	
            <option value="53-mayenne">53-mayenne</option>	
            <option value="54-meurthe-et-moselle">54-meurthe-et-moselle</option>	
            <option value="55-meuse">55-meuse</option>	
            <option value="56-morbihan">56-morbihan</option>	
            <option value="57-moselle">57-moselle</option>	
            <option value="58-nievre">58-nievre</option>	
            <option value="59-nord">59-nord</option>	
            <option value="60-oise">60-oise</option>	
            <option value="61-alencon ">61-alencon </option>	
            <option value="62-pas-de-calais">62-pas-de-calais</option>	
            <option value="63-puy-de-dome">63-puy-de-dome</option>	
            <option value="64-pyrenees-atlantiques">64-pyrenees-atlantiques</option>	
            <option value="65-hautes-pyrenees">65-hautes-pyrenees</option>	
            <option value="66-pyrenees-orientales">66-pyrenees-orientales</option>	
            <option value="67-bas-rhin">67-bas-rhin</option>	
            <option value="68-haut-rhin">68-haut-rhin</option>	
            <option value="69-rhone">69-rhone</option>	
            <option value="70-haute-saone">70-haute-saone</option>	
            <option value="71-saone-et-loire">71-saone-et-loire</option>	
            <option value="72-sarthe">72-sarthe</option>	
            <option value="73-savoie">73-savoie</option>	
            <option value="74-haute-savoie">74-haute-savoie</option>	
            <option value="75-paris">75-paris</option>	
            <option value="76-seine-maritime">76-seine-maritime</option>	
            <option value="77-seine-et-marne">77-seine-et-marne</option>	
            <option value="78-yvelines">78-yvelines</option>	
            <option value="79-deux-sevres">79-deux-sevres</option>	
            <option value="80-somme">80-somme</option>	
            <option value="81-tarn">81-tarn</option>	
            <option value="82-tarn-et-garonne">82-tarn-et-garonne</option>	
            <option value="83-var">83-var	</option>	
            <option value="84-vaucluse">84-vaucluse</option>	
            <option value="85-vendee">85-vendee</option>	
            <option value="86-vienne">86-vienne</option>	
            <option value="87-haute-vienne">87-haute-vienne</option>	
            <option value="88-vosges">88-vosges</option>	
            <option value="89-yonne">89-yonne</option>	
            <option value="90-territoire-de-belfort">90-territoire-de-belfort</option>	
            <option value="91-essonne">91-essonne</option>	
            <option value="92-hauts-de-seine">92-hauts-de-seine	</option>	
            <option value="93-seine-saint-denis">93-seine-saint-denis</option>	
            <option value="94-val-de-marne">94-val-de-marne</option>	
            <option value="95-val-d-oise">95-val-d-oise</option>	
            <option value="Belgique - Province Flandre occidentale">Belgique - Province Flandre occidentale</option>	
            <option value="Belgique - Province Flandre orientale">Belgique - Province Flandre orientale</option>	
            <option value="Belgique - Province Anvers">Belgique - Province Anvers</option>	
            <option value="Belgique - Province Namur">Belgique - Province Namur</option>	
            <option value="Belgique - Province Liege">Belgique - Province Liege</option>	
            <option value="Belgique - Province Limburg">Belgique - Province Limburg</option>	
            <option value="Belgique - Province Brabant wallon">Belgique - Province Brabant wallon</option>	
            <option value="Belgique - Province Hainaut">Belgique - Province Hainaut</option>	
            <option value="Belgique - Province Brabant flamant">Belgique - Province Brabant flamant</option>	
            <option value=" Belgique - Province Luxembourg	">Belgique - Province Luxembourg	</option>	
            <option value="GD Luxembourg">GD Luxembourg</option>	
            <option value="suisse">suisse</option>																																								
        </select>
        <input type="hidden" name="gclid" id="gclid">
        <input type="submit" class="fg_submit" name="submit" value="DECOUVRIR NOS TARIFS">	
        <div id="message_global" class="message"></div>						       
      </form>  
    </div> 
    <script src="../js/traitement.js"></script>
</body>
</html>

   

