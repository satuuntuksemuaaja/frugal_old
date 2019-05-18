<?php

$data = "
<div class='page'>
<center><img src='".public_path()."/logo.png'/></center>
<h3>Countertop Selections</h3>
<p style='font-size: 12px'>";
if ($quote->picking_slab == 'Yes' || $quote->picking_slab == 'Undecided')
$data .= "<img src='".public_path()."/images/checkbox.png' style='padding:5px'> <b>Slab will be chosen by customer</b> :
	By choosing your slab from one of our distributors you must fill out the form on the last page and send to the
	email or fax on the form. This way we can ensure you receive the material you have chosen. If you do not receive a confirmation
	back that we have received the form within a week, please notify us immediately so we can have them shipped to us. 
<br/>";
if ($quote->picking_slab == 'No')
$data .= "<img src='".public_path()."/images/checkbox.png' style='padding:5px'> <b>Customer declines option to select slab and understands that granite is a natural stone and that color varies: </b>
Countertop price will be adjusted after a template is completed by countertop contractor at pp/sqft amount specified above.	Figuring countertops is not an
		exact science. Many factors go into determining price such as waste and layout of slab. Frugal Kitchens does its best to get as close to the
		amount of countertops needed for your kitchen.
<br/>";
$data .= "    
	<img src='".public_path()."/images/checkbox.png' style='padding:5px'>
		If counters go over allotted amount, I agree that Frugal Kitchens can continue with my project and I will pay the additional cost up to 5 sq. ft. If
		it exceeds 5 sq ft, then Frugal Kitchens will send me a change order with the additional cost. If I do not authorize the change order immediately then I
		understand my project will be delayed.
<br/>
<img src='".public_path()."/images/checkbox.png' style='padding:5px'> 		If counters go over the allotted amount, then Frugal Kitchens is no longer able to continue until all change orders have been
		signed and received. <b>I understand this could delay my project if I do not authorize any and all change orders immediately.</b>
<br/>
	  Slabs for quartz are only available in 55\" x 120\". Due to this, there are likely to be more seams than in granite. Please be present when counter
		contractor is making the template to confirm seam locations. If less seams are requested and available, additional slab costs could occur.

<br/></p>

<p>
<b>[______] I, {$quote->lead->customer->name}, 
	understand that if I do not pick a slab, that the slab chosen will most likely not match <i>exactly</i> to the sample found in the showroom. Granite is a naturally 
	ocurring element that varies in pigment and an exact match is not guaranteed.</b>
</p>
<div style='font-size: 11px;'>
<h4>Natural Granite Disclaimer:</h4>
Granite is a coarse, crystalline rock composed primarily of quartz and feldspar. It forms from slowly cooling magma that is subjected to extreme pressures deep beneath the earth's surface. Because it is a natural material it is subject to variation in mineral composition affecting color, flecks, and other aspects of appearance. No two granite/ marble pieces are alike - making each natural granite vanity top a beautiful, one of a kind masterpiece.
<br/><br/>
<b>Granite Countertops Care and Maintenance:</b><br/>
    Blot up spills immediately: Spilling anything other than water or mild soaps, especially acidic substances must be avoided on these countertops. Substances like wine, tomato sauce, fruit juices, alcoholic beverages, coffee and soft drinks won't necessarily etch the granite like they do with marble, but they can stain the surface if neglected. Moreover, cooking oils can also leave their stains, if not wiped up immediately.<br/><br/>
    Sponge or soft cloth for cleaning: For regular cleaning as well as blotting up spilled liquids, paper towels, sponge or soft cloth must be used. Damp rags can be used to remove sticky residue from the countertop. Use warm water and mild soap to clean the granite. However, excessive and repeated use of soap can cause the surface to become dull. Steel wool or other cleaning products should not be used to clean the surface.<br/><br/>
    Avoid harsh cleaning products: Many common household cleaners, such as bleach, kitchen degreasers and glass cleaners contain acids, alkalies and other chemicals. These harsh cleaners can degrade the sealer, thereby making the granite susceptible to staining. Bathroom, grout, tile or tub cleaners must be strictly avoided. Moreover, ammonia, vinegar, orange or lemon must also not be used as cleaners.<br/><br/>
    Avoid adding weight to countertop edges: It is important to avoid putting unnecessary weight on the edges of the countertops. Increased pressure and weight can lead to damage of the edges. Activities such as using the countertop to climb up and clean something or reach a shelf, grabbing on to the countertop for balance,etc. must be avoided. All this can cause the attractiveness of the granite to diminish.<br/><br/>
    Use cutting boards: Granite is scratch resistant; however, this does not imply that one can use the countertop  in place of a cutting board. Cutting boards must be used and all possibilities of causing scratches must be avoided. Moreover, cutting on granite will not only dull the stone, but will also damage the knives' edges.
    <br/><br/>
    Use hot pads or trivets: Granite countertops can withstand heat very well, unlike other surfaces. Granite is a hard stone and can take tons of abuse, without getting damaged. However, the granite surface comprises some soft, thin strips of granite. These thin strips lack enough surface area to absorb all the heat from the piping hot pots and pans, thereby resulting into chipping and scratching of the lustrous surface. Generally scratches are not formed so easily, however, it is advisable to use trivets or hot pads.
    <br/><br/>
    Apply sealant for protection: Application of sealant on granite countertops, either semiannually or annually, helps protect the granite surface from damage. When solvent based sealers are applied to the countertops, the surface achieves a new, sparkling look. Sealers do not eliminate the danger of staining; however, they do increase the window period of stain blotting time. Sealers generally need to be reapplied every year.
</div>
</div>
";

echo $data;