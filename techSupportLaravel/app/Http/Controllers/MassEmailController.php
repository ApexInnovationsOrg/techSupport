<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\SupportTicket;

use App\Helpers\EmailParser;
use App\Helpers\SessionHelper;

use DB;
use App\Employee;
use App\Users;
use App\SupportTypes;
use App\SupportTicketTransfer;

use Illuminate\Http\Request as HttpRequest;

use Request;
use Auth;
use Validator;
use Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;


// use PhpImap\Mailbox as ImapMailbox;
// use PhpImap\IncomingMail;
// use PhpImap\IncomingMailAttachment;


class MassEmailController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('emails');
	}

	public function sendEmail($emailType = null)
	{
		switch ($emailType)
		{
			case 'test':
				// $users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login LIKE 'eddie@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.* FROM Users AS U WHERE Login LIKE 'eddie@apexinnovations.com'");


				// dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					Mail::send('emails.impulse2TQ.careminderEmail',['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('imPULSE 2.0 Test Question Analysis and Adjustments');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'impulsetq');

			        });
				}
				return redirect('/email')->with('message','test email successfully sent'); 




			case 'imp2user':
				$users = DB::select("
									SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login
									FROM Users U
									INNER JOIN LicenseSeats LS ON LS.UserID = U.ID
									INNER JOIN LicensePeriods LP ON LP.ID = LS.LicensePeriodID
									INNER JOIN Licenses L ON L.ID = LP.LicenseID
									INNER JOIN Products P ON P.ID = L.ProductID
									WHERE Login NOT LIKE '%apex%'
									AND LMS = 'N'
									AND P.ID = 9
									AND (LS.ExpirationDate > NOW() OR LP.EndDate > NOW())
									AND U.Login NOT IN('dkrueger@mchhs.org', 'dmdd5@cox.net', 'dmdd4@cox.net', 'chase_champagne@yahoo.com', 'Corinne422@yahoo.com', 'l1vasque@sbch.org', 'g3brown@sbch.org', 'm1swann@chs.org', 'avelazqu@sbch.org', 'b1facund@sbch.org', 'skylar.comeaux@yahoo.com', 'rbourgeois@lgmc.org', 'manguyen@lgh.org', 'lhsuire@lgh.org', 'shargroder@lgh.org', 'prroberts@lgh.org', 'tara.m@hotmail.com', 'rachel.thomasian@ihs.gov', 'robin.sanders@ihs.gov', 'charla.wauqua@ihs.gov', 'kristi.bass@ihs.gov', 'elizabeth.howell@ihs.gov', 'hayden.hughes@ihs.gov', 'mandap3@gmail.com', 'hawkinsrn@gmail.com', 'christina.bobek@gmail.com', 'gary.trisbr@gmail.com', 'julie.fitzpatrick@stalphonsus.org', 'julie.fitzpatrick@saintalphonsus.org', 'brian.sestrich@va.gov', 'suzzette.mosor@va.gov', 'Elizabeth.Finley4@va.gov', 'Glenda.Hargrove@va.gov', 'Jacqueline.Harmon-Askew2@va.gov', 'Deshondra.Bryant@va.gov', 'sxp2248@bjc.org', 'pmikolajczak@slhv.com', 'jakelaurrell@gmail.com', 'levi.ammons@bjc.org', 'Kyle.Chadwell@bjc.org', 'matthew.morris@bjc.org', 'mike.lorenz04@gmail.com', 'chnecard@gmail.com', 'Brenda.Harlow@bjc.org', 'Ronnie.Bivens@bjc.org', 'Katy.Herman@bjc.org', 'rtmukerji@yahoo.com', 'george7a@hotmail.com', 'Kayla.Goewey@bjc.org', 'Sandra.LindleyStone@bjc.org', 'joanne.kitzul@interiorhealth.ca', 'jsmidt@mercydesmoines.org', 'jborchers@mercydesmoines.org', 'ajohns@mercydesmoines.org', 'mforkner@mercydesmoines.org', 'terra777@aol.com', 'eliewer@mercydesmoines.org', 'bmutapcic@mercydesmoines.org', 'amodlin@mercydesmoines.org', 'syoungblood@mercydesmoines.org', 'rachael.reicks@gmail.com', 'hferns@mercydesmoines.org', 'bpeterman90@gmail.com', 'jdavis6@mercydesmoines.org', 'mthomas4@mercydesmoines.org', 'jostrander07@gmail.com', 'jross@mercydesmoines.org', 'cburroughs@mercydesmoines.org', 'kheuton@mercydesmoines.org', 'sglover@mercydesmoines.org', 'mcraver@mercydesmoines.org', 'ajenkins@mercydesmoines.org', 'tiffani_brush@hotmail.com', 'mortozaa@uni.edu', 'Ablanchard@mercydesmoines.org', 'anna.serge@mercydesmoines.org', 'AWEEKS@MERCYDESMOINES.ORG', 'wmaeder@yahoo.com', 'runangelarun17@aim.com', 'dhopper@mercydesmoines.org', 'kbuck@mercydesmoines.org', 'toriger@Mercydesmoines.org', 'jskelton@mercydesmoines.org', 'leatirevold@hotmail.com', 'kaaynangle@gmail.com', 'Kbauer2@mercydesmoines.org', 'SSSTABENOW@MERCYDESMOINES.ORG', 'tanyalandin@mercydesmoines.org', 'tfoote@d-c-h.org', 'kanderson6@krmc.org', 'torranderson@krmc.org', 'lbennett@krmc.org', 'hbrandt@krmc.org', 'dhannam@krmc.org', 'rhave@krmc.org', 'melmcmanaway@yahoo.com', 'hfebach@krmc.org', 'knallymadigan@krmc.org', 'twehrmann@krmc.org', 'rwhalen@krmc.org', 'jmahowald@krmc.org', 'manderson@krmc.org', 'jamiemahowald12@gmail.com', 'bigskykate@gmail.com', 'lindsay.bennett17@gmail.com', 'hedwards@krmc.org', 'spring_gibbs@yahoo.com', 'mt_carla@yahoo.com', 'mandy.pokorny@outlook.com', 'rhiannoncurrie@yahoo.com', 'bakerb@unbc.ca', 'higgin3@unbc.ca', 'ReeseTr@health.missouri.edu', 'moenm@health.missouri.edu', 'stumbaughj@health.missouri.edu', 'aquinok@health.missouri.edu', 'brumitc@health.missouri.edu', 'krausck@health.missouri.edu', 'shrumh@health.missouri.edu', 'mcrobertslm@health.missouri.edu', 'onealca@health.missouri.edu', 'britney.mcgrath@physiciansregional.com', 'chantel.emery@physiciansregional.com', 'mary.fingeroff@physiciansregional.com', 'sara.gray@physiciansregional.com', 'james.chapman@physiciansregional.com', 'heidi.bach@physiciansregional.com', 'staci.yarn@physiciansregional.com', 'john.howe@physiciansregional.com', 'wendy.french@physiciansregional.com', 'yanetsis.oquendo@physiciansregional.com', 'cynthia.morgan@physiciansregional.com', 'debra.mettler@physiciansregional.com', 'darline.rosenbert@physiciansregional.com', 'joseph.kunkel@physiciansregional.com', 'daniel.karlstad@physiciansregional.com', 'tricia.kemp@physiciansregional.com', 'matthew.haines@physiciansregional.com', 'kristin.gallanosa@physiciansregional.com', 'tia.woodford@physiciansregional.com', 'patricia.sutton@physiciansregional.com', 'luisa.bouchard@physiciansregional.com', 'lynn.rapoza@physiciansregional.com', 'Carolina.zuferri@physiciansregional.com', 'erik.martindale@physiciansregional.com', 'joseph.kennedy@physiciansregional.com', 'sanche.bell@physiciansregional.com', 'lexie.nemcek@physiciansregional.com', 'kacie.holtzclaw@physiciansregional.com', 'susan.vacker@physiciansregional.com', 'sandra.maldonado@physiciansregional.com', 'Rochelle.Hatcher@physiciansregional.com', 'steven.sawyer@physiciansregional.com', 'theodore.vanderkamp@physiciansregional.com', 'mary.ferrebee@physiciansregional.com', 'thomas.junkins@physiciansregional.com', 'tara.molden@physiciansregional.com', 'jessica.shaw@physiciansregional.com', 'donnie.bowling@physiciansregional.com', 'felicia.keen@physiciansregional.com', 'michael.peddle@physiciansregional.com', 'kayla.lewis@physiciansregional.com', 'sylvia.jordan@physiciansregional.com', 'frantz.duberceau@physiciansregional.com', 'cscott61@hma.com', 'tammy.hunger@physiciansregional.com', 'angela.latino@physiciansregional.com', 'chbrophy@abingtonhealth.org', 'jamie.sonewald@UHhospitals.org', 'GENEVAS@BAYLORHEALTH.EDU', 'Kenetria.Woodard@BaylorHealth.edu', 'George.Nyambane@BaylorHealth.edu', 'JakeM@BaylorHealth.edu', 'Erika.Whiteman@BaylorHealth.edu', 'AmyWa@BaylorHealth.edu', 'ChristinaM.Guynes@BaylorHealth.edu', 'Brett.Perkins@BaylorHealth.edu', 'Darren.Nix@BaylorHealth.edu', 'ShannonL.Williams@BaylorHealth.edu', 'Maureen.Puma@BaylorHealth.edu', 'Adam.Starnes@BaylorHealth.edu', 'Rajwant.Kaur@BaylorHealth.edu', 'Jessica.Stroud@baylorhealth.edu', 'Ian.McKnight@baylorhealth.edu', 'Christopher.Hardin@baylorhealth.edu', 'Temitope.Badejo@BSWHealth.org', 'Kristine.Bernardo@BSWHealth.org', 'Aaron.Longnecker@BSWHealth.org', 'Brittany.Johnson@BSWHealth.org', 'Debra.James@BSWHealth.org', 'Megan.Linebarger@BSWHealth.org', 'Lamekia.Curry@BSWHealth.org', 'Aaron.Curry@BSWHealth.org', 'Pauline.Baculi@BSWHealth.org', 'Vanessa.Bersamina@BSWHealth.org', 'Anthony.Bonadona@BSWHealth.org', 'Justina.Brown@BSWHealth.org', 'Ariel.Cadena@BSWHealth.org', 'Steve.Cary@BSWHealth.org', 'Hannah.Connell@BSWHealth.org', 'Christian.Cottey@BSWHealth.org', 'Ashley.Cozby@BSWHealth.org', 'Angie.Davis@BSWHealth.org', 'Ogechi.Ede1@BSWHealth.org', 'Francis.Elona@BSWHealth.org', 'Khadija.Finger@BSWHealth.org', 'Anne.Ginete@BSWHealth.org', 'Erica.Gonzales@BSWHealth.org', 'Katherine.Hinckley@BSWHealth.org', 'Kelsey.Holbrook@BSWHealth.org', 'Kyle.Johnson@BSWHealth.org', 'Ashley.Mackey@BSWHealth.org', 'Ashley.Magee@BSWHealth.org', 'Rachel.Miller@BSWHealth.org', 'Natzely.Monrroy@BSWHealth.org', 'Emily.Mueller@BSWHealth.org', 'Dejana.Neskovic@BSWHealth.org', 'Camille.Norris@BSWHealth.org', 'Noel.Parks@BSWHealth.org', 'Timothy.Pham@BSWHealth.org', 'Nakia.PittmanShaver1@BSWHealth.org', 'Ashley.Pyeatt@BSWHealth.org', 'Benjamin.Rieke@BSWHealth.org', 'Nicole.Roberts@BSWHealth.org', 'Morgan.Salerno1@BSWHealth.org', 'Rebecca.Shields@BSWHealth.org', 'Juliana.Smith@BSWHealth.org', 'Kimberly.Snyder@BSWHealth.org', 'Katy.Somerville@BSWHealth.org', 'Paige.Tatman1@BSWHealth.org', 'Shelby.Varvel1@BSWHealth.org', 'Erin.Wainscott1@BSWHealth.org', 'Dcayla.Ward@BSWHealth.org', 'Brittany.White1@BSWHealth.org', 'Nathan.Winters@BSWHealth.org', 'Catherine.Woerner@BSWHealth.org', 'Seth.Zhanel@BSWHealth.org', 'Lauren.Ziemian@BSWHealth.org', 'Sarajane.Campbell@BSWHealth.org', 'Emily.Fuson@BSWHealth.org', 'Linta.Xavier@baylorhealth.edu', 'Katheryn.Klenda@baylorhealth.edu', 'Lisa.Ueda@baylorhealth.edu', 'karen.davison@bswhealth.org', 'elizabeth.hodges@bswhealth.org', 'jeremy.hudson@bswhealth.org', 'anna.doty@bswhealth.org', 'branef.patton@bswhealth.org', 'blessie.brobo@bswhealth.org', 'shajimon.alapatt@baylorhealth.edu', 'roberto.garcia@baylorhealth.edu', 'Adrienne.Bondoc@baylorhealth.edu', 'Diana.Tyler@baylorhealth.edu', 'Kwan.Kim@baylorhealth.edu', 'Hee.Lee@baylorhealth.edu', 'Monique.Garlock@baylorhealth.edu', 'MichelleE.Henscheid@baylorhealth.edu', 'Margaret.ONeil@baylorhealth.edu', 'Evelyne.Ongwae@baylorhealth.edu', 'Brandy.Snyder@baylorhealth.edu', 'Rachel.Valenzuela@baylorhealth.edu', 'Molly.Walker@baylorhealth.edu', 'Guyanell.Wigley@baylorhealth.edu', 'kendra.johnson@bswhealth.org', 'Amberneisha.Flournoy@BSWHealth.org', 'Francisca.Arzola@BSWHealth.org', 'Brittany.Berry@BSWHealth.org', 'Christian.Burkhardt@BSWHealth.org', 'Macy.Crabb@BSWHealth.org', 'Rebecca.Darring1@BSWHealth.org', 'Kelli.Davis2@BSWHealth.org', 'Liza.Garza1@BSWHealth.org', 'Meredith.Gregory@BSWHealth.org', 'Nam.Hoang@BSWHealth.org', 'Alice.Kalu@BSWHealth.org', 'Carson.Magruder@BSWHealth.org', 'Jonathan.Morales@BSWHealth.org', 'Kim.Ngo@BSWHealth.org', 'Nkoli.Okoli@BSWHealth.org', 'Heather.Richardson@BSWHealth.org', 'Juni.Shrestha1@BSWHealth.org', 'Arielle.Smith@BSWHealth.org', 'Dominic.Zuniga@BSWHealth.org', 'Stephanie.Jung@BSWHealth.org', 'Penny.Ward@baylorhealth.edu', 'Crystal.Mai@baylorhealth.edu', 'Carmen.George@baylorhealth.edu', 'Jorizza.Castro@baylorhealth.edu', 'Victoria.Webb@baylorhealth.edu', 'Heather.Lewis@baylorhealth.edu', 'Michele.McKean@baylorhealth.edu', 'Judith.Olson@baylorhealth.edu', 'Ashley.Hurley@bswhealth.org', 'Karen.Horton@baylorhealth.edu', 'Nithphaphone.Ponce@baylorhealth.edu', 'Kristen.Eichenseer@baylorhealth.edu', 'Amanda.Kilgore@baylorhealth.edu', 'Jennifer.Olaughlin@baylorhealth.edu', 'Kristen.Hairelson@baylorhealth.edu', 'Jessica.Heredia@BSWhealth.org', 'Alyson.Lauver@BSWHealth.org', 'Melissa.Regier@BSWHealth.org', 'Katherine.Summerlin@BSWHealth.org', 'pmhz1962@gmail.com', 'tonya.cullen1@bswhealth.org', 'lisa.dunn@bswhealth.org', 'jenniferlrone@yahoo.com', 'mfskin@yahoo.com', 'joshua.carter@BSWhealth.org', 'katie.euerle@baylorhealth.edu', 'kay.mccoy@BSWHealth.org', 'Tcren01@gmail.com', 'Jenjadekar@yahoo.com', 'tonya.cullen1@bswh.org', 'bmcbridern@yahoo.com', 'srcinva@gmail.com', 'ogreene685@gmail.com', 'Ingrid.Crawford@BSWHealth.org', 'Mirna.Aguilar@BSWHealth.org', 'chris.ledbetter@bswhealth.org', 'Daphne.Weed@baylorhealth.edu', 'Brittany.Zingler@baylorhealth.edu', 'bindu.jose@bswhealth.org', 'Emily.mogeni@bswhealth.org', 'amy.wilkins@bswhealth.org', 'becky.hunter@bswhealth.org', 'Thomas.Varughese@BSWHealth.org', 'Eva.Schauer1@BSWHealth.org', 'Kristina.Thornton@BSWHealth.org', 'Priscilla.Namuwaya@BSWHealth.org', 'Moncy.Jacob@BSWHealth.org', 'Stephanie.Steele@BSWHealth.org', 'Jessica.Collins@BSWHealth.org', 'Evelyn.George@BSWHealth.org', 'Magen.Isaacs1@BSWHealth.org', 'Duy.Tran1@BSWHealth.org', 'Megan.Mooney@BSWHealth.org', 'seray.jah@bswhealth.org', 'Jessica.Kim@BSWHealth.org', 'Jodie.Ward@BSWHealth.org', 'Bethany.Collop@BSWHealth.org', 'Amanda.Davis@bhcs.com', 'Angela.Montgomery@BSWHealth.org', 'Kristie.Tinh@BSWHealth.org', 'Khanh.Bui@BSWHealth.org', 'Ozza.Mathema1@BSWHealth.org', 'Joshua.Palomo@BSWHealth.org', 'Mireya.Vaughan@BSWHealth.org', 'Olivia.Herrera@BSWHealth.org', 'Yoo.Yang@BSWHealth.org', 'Hany.Kim@BSWHealth.org', 'LaRisha.Glover@BSWHealth.org', 'Rameses.Akbar@baylorhealth.edu', 'HAYLEY.KILLINGSWORTH@BAYLORHEALTH.EDU', 'RONALD.KWAMBAI@BAYLORHEALTH.EDU', 'KAILEA.SHAW@BAYLORHEALTH.EDU', 'TAIJA.TEGETHOFF@BAYLORHEALTH.EDU', 'laquarion.bradley@baylorhealth.edu', 'Brittney.Baker@baylorhealth.edu', 'Jennifer.Chesterman@baylorhealth.edu', 'Neha.Patel@baylorhealth.edu', 'Tina.Watson@baylorhealth.edu', 'psmithabi@hotmail.com', 'kristina.creech@baylorhealth.edu', 'peggy.gentzel@baylorhealth.edu', 'Symetria.Campbell@Baylorhealth.edu', 'Brittney.Musquiz@baylorhealth.edu', 'KATELYN.HEITZMAN@BAYLORHEALTH.EDU', 'HOLLY.LONDT@BAYLORHEALTH.EDU', 'KIAK.MALVEAUX@BAYLORHEALTH.EDU', 'FRANCES.SERRANO@BAYLORHEALTH.EDU', 'Sally.Dye@baylorhealth.edu', 'Ashley.Woodyard@baylorhealth.edu', 'Billy.Jones@baylorhealth.edu', 'Doneisha.Williams@Baylorhealth.edu', 'Kirstie.Buggar@BSWHealth.org', 'HeatherO@BaylorHealth.edu', 'Anglia.Justice@BSWHealth.org', 'Jessica.Creel@BSWHealth.org', 'Terrell.Hinton@BSWHealth.org', 'Jeanette.Linthicum@BSWHealth.org', 'James.Green@BSWHealth.org', 'Stormy.Williams@BSWHealth.org', 'Edward.Nyansimera@BSWHealth.org', 'Deborah.Davis@BSWHealth.org', 'Thelma.Valvera@BSWHealth.org', 'Ricinda.Rodriguez@BSWHealth.org', 'Peggy.McAtee@BSWHealth.org', 'John.Rudy@BSWHealth.org', 'Henry.Viejo@BSWHealth.org', 'Montriz.Coleman@BSWHealth.org', 'Nadia.Hopper@BSWHealth.org', 'TWILLA.LOVE@BSWHEALTH.ORG', 'Elizabeth.Martinez1@BSWhealth.org', 'Sheritta.Rush@BSWHealth.org', 'Jessiebet.Muniz@BSWHealth.org', 'Catalina.Hernandez@BSWHealth.org', 'Jennifer.Coble@BSWHealth.org', 'Walid.Elsabee@BSWHealth.org', 'Belinda.Gomez@BSWHealth.org', 'Antoinette.Hart@BSWHealth.org', 'Kaitlyn.Sessum@BSWHealth.org', 'Korine.Drake@BSWHealth.org', 'David.Hernandez@BSWHealth.org', 'NATHAN.THORP@BSWHEALTH.ORG', 'Sheena.Hoar@BSWhealth.org', 'Marissa.Figueroa@BSWHealth.org', 'BRANDI.COBB1@BSWHEALTH.ORG', 'COLETTE.DAVIS@BSWHEALTH.ORG', 'RIKKI.ELSABEE@BSWHEALTH.ORG', 'LAURA.GIBBON@BSWHEALTH.ORG', 'NATASHA.NOLLY@BSWHEALTH.ORG', 'ERIN.KIRKPATRICK@BSWHEALTH.ORG', 'EMILY.MERRITT@BSWHEALTH.ORG', 'ROBERT.MONTOYA@BSWHEALTH.ORG', 'CHRISTI.SCARBOROUGH@BSWHEALTH.ORG', 'RYAN.STUTES@BSWHEALTH.ORG', 'ELISSA.WOO1@BSWHEALTH.ORG', 'JENNIFER.WILLIAMS2@BSWHEALTH.ORG', 'Amanda.VonHeal@mylrh.org', 'marie.able@mylrh.org', 'donna.faviere@mylrh.org', 'stacey.wells@mylrh.org', 'kaamir@browardhealth.org', 'abuckham@browardhealth.org', 'jdigiorgio@browardhealth.org', 'sforum@browardhealth.org', 'mfoster@browardhealth.org', 'ggardner@browardhealth.org', 'ghergerton@browardhealth.org', 'dnoonan@browardhealth.org', 'pramdas@browardhealth.org', 'pswinton@browardhealth.org', 'jvedaee@browardhealth.org', 'dweinstein@browardhealth.org', 'aworthley@browardhealth.org', 'cbarcomb@mhs.net', 'NatBrown@mhs.net', 'mucajuste@mhs.net', 'Aaron.Bartoe@hcahealthcare.com', 'Sherry.Brannen@hcahealthcare.com', 'Donald.Eberhardt@hcahealthcare.com', 'Aileen.GelyReyes@hcahealthcare.com', 'Lauren.Langford@hcahealthcare.com', 'Petra.Ramdhanie@hcahealthcare.com', 'Armando.TorresFelix@hcahealthcare.com', 'Abigail.Vazquez2@hcahealthcare.com', 'Rebecca.Pasantes@hcahealthcare.com', 'Lawanda.Jackson2@hcahealthcare.com', 'Maria.AtkinsonNunez@hcahealthcare.com', 'Evelyn.Bateman@hcahealthcare.com', 'Louna.Jean@hcahealthcare.com', 'Angeles.Rocha@hcahealthcare.com', 'Shaun.Poe@hcahealthcare.com', 'Bryan.Benson@hcahealthcare.com', 'Rosanna.Zapata@hcahealthcare.com', 'Joan.Kerr@hcahealthcare.com', 'Lenitza.Pereira@hcahealthcare.com', 'Sergio.Concepcion2@hcahealthcare.com', 'Nikita.Patel@hcahealthcare.com', 'Chris.Clausen@hcahealthcare.com', 'Cheryna.Hamilton@hcahealthcare.com', 'Susan.Sopel@hcahealthcare.com', 'Jean.TorresDavila@hcahealthcare.com', 'Lovely.Robert@hcahealthcare.com', 'Veda.Rivera@hcahealthcare.com', 'Cinda.Kavurt@hcahealthcare.com', 'Rommel.DeLaRosa@hcahealthcare.com', 'Matthew.Winter@hcahealthcare.com', 'susan.knaus@bellin.org', 'rachael.perrault@bellin.org', 'carrie.wolfgram@bellin.org', 'heidi.flick@bellin.org', 'jordan.anderson@bellin.org', 'jennifer.emmes@bellin.org', 'anjali.bircher@bellin.org', 'Timothy.Coenen@bellin.org', 'dean.weycker@bellin.org', 'david.johnson@bellin.org', 'cnelson.do@gmail.com', 'coldfront1@embarqmail.com', 'david.dresel@health.mo.gov', 'paticrews@gmail.com', 'awildcatrn@gmail.com', 'McCordJ@lincolnu.edu', 'rkaruparthi@yahoo.com', 'mriegelrn@aol.com', 'Breg2@aol.com', 'Cardio1946@gmail.com', 'heartdok@gmail.com', 'Sandy.Fancher@kadlecmed.org', 'SARA.YODER@kadlecmed.org', 'kayla.christman@readinghealth.org', 'tabitha.york@readinghealth.org', 'russell.myers@readinghealth.org', 'erica.hersh@readinghealth.org', 'travis.chapa@readinghealth.org', 'lelian.hanna-siha@readinghealth.org', 'alice.arinze-ofili2@readinghealth.org', 'Danielle.Weller2@readinghealth.org', 'brendan.barton@readinghealth.org', 'danielle.kohler@readinghealth.org', 'ross.mcallister@readinghealth.org', 'Jessica.Fair@readinghealth.org', 'lnoliver@sbrmc.org', 'brandy.johnson@dignityhealth.org', 'aneudauer@tvhd.org', 'hmoon@tvhd.org', 'ricardo.hernandez@bjc.org', 'constantd@sah.on.ca', 'wilej@sah.on.ca', 'fleminga@sah.on.ca', 'senkot@sah.on.ca', 'rossla@sah.on.ca', 'bnnewsom@seton.org', 'ragordon@seton.org', 'araquepo@seton.org', 'tdeluca@seton.org', 'msdelong@seton.org', 'jeavery@seton.org', 'RDonahue@seton.org', 'skburke@seton.org', 'ayost@seton.org', 'KGALLMAN@seton.org', 'jlee@seton.org', 'dmelek@seton.org', 'nzuck@seton.org', 'pracosta@seton.org', 'cjrogers@seton.net', 'QEGBUAWA@Seton.org', 'ssluna@seton.org', 'fbutz@seton.org', 'KWilson-mckissock@seton.org', 'skperez@seton.org', 'jumerjumper@yahoo.com', 'CRiggs@seton.org', 'remontgomery@seton.org', 'ale@seton.org', 'cjkeene@seton.org', 'lisabrogan01@yahoo.com', 'llewicki@seton.org', 'ecsalgado@seton.org', 'sculley@seton.org', 'Amiller4@seton.org', 'JOSCELYN.JONES@YAHOO.COM', 'mthate@seton.org', 'MaCordes@seton.org', 'dmsmith@seton.org', 'Josiesereg@gmail.com', 'AnEverett@seton.org', 'dabaluh@seton.org', 'sholsapple@seton.org', 'tlbruno1@seton.org', 'mnbeckham@seton.org', 'DrGrant@seton.org', 'etownend@seton.org', 'bwuollet@seton.org', 'AcBarron@seton.org', 'lbbourg@seton.org', 'rwillows@seton.org', 'AWittmuss@seton.org', 'panthony@seton.org', 'jdeoff@seton.org', 'orgarza@seton.org', 'jdwoods@seton.org', 'MEGuthrie@seton.org', 'aburge@seton.org', 'jaBrewer@seton.org', 'DAjaimes-jaimes@seton.org', 'amshelton@seton.org', 'JnPorter@Seton.org', 'JMiller2@seton.org', 'NgDyer@seton.org', 'saward@seton.org', 'EDennis@seton.org', 'spontiberos@seton.org', 'AVilleda@seton.org', 'LThurman@seton.org', 'TsWong@seton.org', 'Salasp@live.com', 'ashook@seaton.org', 'SAnwar@seton.org', 'BRoy@seton.org', 'clhardeman@seton.org', 'mdpopp@seton.org', 'iwidjaja@seton.org', 'abaguilar@seton.org', 'MaRomero2@seton.org', 'crh2191@yahoo.com', 'aarevalo@seton.org', 'jay.diehl@metrogr.org', 'osmin.reyes@metrogr.org', 'sarah.iwema@metrogr.org', 'rachel.tibbe@metrogr.org', 'ashley.pattison@metrogr.org', 'chelsea.ellis@metrogr.org', 'michele.fifelski@metrogr.org', 'kathryn.merritt@metrogr.org', 'brittany.koonce@metrogr.org', 'jill.kwapis@metrogr.org', 'Joy.Bush@metrogr.org', 'kyle.meyer@metrogr.org', 'daniel.plaggemars@metrogr.org', 'Rachel.Alberda@metrogr.org', 'Brittany.Ramos@metrogr.org', 'Ashleigh.Buckius@metrogr.org', 'stephanie.schneller@metrogr.org', 'daniel.devries@metrogr.org', 'taylor.martindale@metrogr.org', 'abigail.tatroe@metrogr.org', 'Cassandra.hill@metrogr.org', 'elizabeth.kimball@metrogr.org', 'nicole.kendall@metrogr.org', 'cierra.brown@metrogr.org', 'mrsmillerracing@yahoo.com', 'cbj5@buckeye-express.com', 'megan.savage@promedica.org', 'erica.shoupe@promedica.org', 'joshua.ovens@promedica.org', 'lindsay.hohlbein@promedica.org', 'oneilltv@hotmail.com', 'haleyk30@gmail.com', 'saraharendt@live.com', 'emily_mckarns@yahoo.com', 'Michelle.Corser@Promedica.org', 'tonia.sprague@gmail.com', 'mandyreitzel@gmail.com', 'jeremy.hemminger@promedica.org', 'amanda.harpel@promedica.org', 'jayne.johnoff@promedica.org', 'SusGob711@aol.com', 'jess8724@yahoo.com', 'LAMAR.GOODWIN@PROMEDICA.ORG', 'bradley.asbury19@gmail.com', 'amethyst.upchurch@promedica.org', 'Rmgsam@bex.net', 'mira.ulmer@promedica.org', 'cratt76@gmail.com', 'kara.osterhout@promedica.org', 'Runner_4life@embargmail.com', 'sarah.stine@promedica.org', 'bobbi.garcia@promedica.org', 'kelly.fruth@promedica.org', 'josh.erickson@promedica.org', 'karen.gilbert@promedica.org', 'adrianne.mahl@promedica.org', 'angela.jordan@promedica.org', 'kimberly.dye@promedica.org', 'micaela.dixon@promedica.org', 'michael.wilkins@promedica.org', 'wilkinsml2@hotmail.com', 'michelle.flis@promedica.org', 'heather.campbell@promedica.org', 'lydia.mock@promedica.org', 'tracy.horvath@promedica.org', 'limdsay.kernyo@promedica.org', 'michael.green@promedica.org', 'megan.riegsecker3@gmail.com', 'annie.eicher@promedica.org', 'andrea.aguilar@promedica.org', 'lindsay.kernyo@promedica.org', 'korena.ditmyer@promedica.org', 'bonniegebhart@gmail.com', 'kelly_hodges@elcaminohospital.org', 'justin_stewart@elcaminohospital.org', 'Bonnie_Gebhart@elcaminohospital.org', 'curtis_warren@elcaminohospital.org', 'APRIL_PHAM@ELCAMINOHOSPITAL.ORG', 'Kuiwon_Song@elcaminohospital.org', 'Tracy_Mitchum@elcaminohospital.org', 'Joanne_Turner@elcaminohospital.org', 'naftali_maniquis@elcaminohospital.org', 'tuyen_tran@elcaminohospital.org', 'xin_ru@elcaminohospital.org', 'jason_cruz@elcaminohospital.org', 'sara_rekasis@elcaminohospital.org', 'letha_brown@elcaminohospital.org', 'paula_riley@elcaminohospital.org', 'kathryn_fortier@elcaminohospital.org', 'kaitlyn_mason@elcaminohospital.org', 'ilona_bartnik@elcaminohospital.org', 'Beth_Willy@elcaminohospital.org', 'jabastarmer@hotmail.com', 'ambar_cortez@elcaminohospital.org', 'bennie_kouyate@elcaminohospital.org', 'Kevin_Harris@elcaminohospital.org', 'mtfortner@srhs.com', 'mburrell@srhs.com', 'mcooper40@windstream.net', 'Kathleen.bossier@ololrmc.com', 'kelsey.cockerham@ololrmc.com', 'monicadyne@hotmail.com', 'courtney.williams3@ololrmc.com', 'Kathleen.Hussain@ololrmc.com', 'kaitlyn.thornton@ololrmc.com', 'michael.nganga@us.af.mil', 'johnetta.altizer@lpnt.net', 'sarah.yates@lpnt.net', 'elizabeth.hall2@lpnt.net', 'tiffany.sullivan@lpnt.net', 'troy.augustin@lpnt.net', 'thaddeus.raines@lpnt.net', 'keisha.robertson@lpnt.net', 'riann.cummings@lpnt.net', 'shamille.relf@lpnt.net', 'amber.colley@lpnt.net', 'yanique.lewis@lpnt.net', 'jennifer.thurman@lpnt.net', 'myrtle.monroe@lpnt.net', 'hannah.shaffer@lpnt.net', 'lasherrell.white@lpnt.net', 'james.hixson@lpnt.net', 'christopher.williams2@lpnt.net', 'lachelsa.horne@lpnt.net', 'michael.mizell@lpnt.net', 'edward.hawthorne@lpnt.net', 'mercedes.thomas@lpnt.net', 'angela.eder@lpnt.net', 'tamisha.willingham@lpnt.net', 'everett.moss@lpnt.net', 'joecita.williams@lpnt.net', 'joseph.dibble@lpnt.net', 'shequana.smith@lpnt.net', 'travems@yahoo.com', 'amanda.jones@lpnt.net', 'sharon.baird@lpnt.net', 'Kathe.Godby@lpnt.net', 'alisha.wells@lpnt.net', 'james.goodin@lpnt.net', 'debra.white@lpnt.net', 'william.randolph@lpnt.net', 'harold.walston@lpnt.net', 'lojuanna.vanhook@lpnt.net', 'KIMBERLY.CAMPBELL@LPNT.NET', 'olivia.slagle@lpnt.net', 'kimberly.strunk@lpnt.net', 'katie.collins@lpnt.net', 'angelica.brumley@lpnt.net', 'elaine.teaman@lpnt.net', 'danielle.powell@lpnt.net', 'stacey.tucker@lpnt.net', 'audree.powell@lpnt.net', 'katie.banks@lpnt.net', 'williedemtp@hotmail.com', 'mark.davis630@gmail.com', 'brent.corder@yahoo.com', 'kneal_dad@hotmail.com', 'amyshell701@hotmail.com', 'carlcimarossa@gmail.com', 'SOFIA.SULLIVAN@LPNT.NET', 'leann.anderson@lindsey.edu', 'Frances.Alley@LPNT.NET', 'tattooednurse01@gmail.com', 'ksparks1010@hotmail.com', 'wesleyjamied@yahoo.com', 'jayln.eastham@lpnt.net', 'shea.smith@lpnt.net', 'jon.fowler@lpnt.net', 'lindsey.mcmillion@lpnt.net', 'rkerner@nshs.edu', 'lpersico@nshs.edu', 'mjadelstein@gmail.com', 'jaguila@nshs.edu', 'oscar.alean@gmail.com', 'michaelannarella@mail.adelphi.edu', 'danyaawad@mail.adelphi.edu', 'pcastellano@lions.molloy.edu', 'alice.m.cheng@gmail.com', 'patsy.coppola@gmail.com', 'dec5@optonline.net', 'cassiefarrell851@gmail.com', 'jennifergarcia49@hotmail.com', 'kayla.a.garner@gmail.com', 'diana.giacomino@gmail.com', 'djackerson@gmail.com', 'ewelina.janucik@yahoo.com', 'mjatsko@yahoo.com', 'Jgbd17@gmail.com', 'dcjimen@gmail.com', 'navdeepk92@gmail.com', 'sonyakaur0414@gmail.com', 'nkelly4@nshs.edu', 'amkosmalski@gmail.com', 'nicolemarielitt@gmail.com', 'erinrosemay@gmail.com', 'tiffanymena@mail.adelphi.edu', 'eve.moreira2486@gmail.com', 'joaimee.nagtalon@gmail.com', 'erinashleyobrien@gmail.com', 'p.r.maresca@gmail.com', 'jroman4@nshs.edu', 'car548@nyu.edu', 'christine.samuel27@gmail.com', 'bsilvestri@nshs.edu', 'uszewczyk@nshs.edu', 'jtangredi0823@gmail.com', 'christythottam@gmail.com', 'tomcyv4@gmail.com', 'overbitskaia@yahoo.com', 'tverhey90@gmail.com', 'jwaldma1@gmail.com', 'douglas.wallick@my.liu.edu', 'joelwartenberg@yahoo.com', 'nwoltering@lions.molloy.edu', 'jayearwood19@gmail.com', 'laurenboccio@gmail.com', 'angeladelauro@mail.adelphi.edu', 'hoppera@student.wpunj.edu', 'leean0007@gmail.com', 'iguico527@yahoo.com', 'kregan1@nshs.edu', 'shiva.moshtagh@gmail.com', 'Gamoroso@NSHS.edu', 'jangeles1@nshs.edu', 'Kayla.arnold@student.fairfield.edu', 'sban1@nshs.edu', 'jbernaudo1@NSHS.edu', 'sberroa@NSHS.edu', 'dbowers2@NSHS.edu', 'Kbuhagiar@nshs.edu', 'ABujacich@NSHS.edu', 'ECalhoun1@NSHS.edu', 'jcallahan4@NSHS.edu', 'ccastro6@NSHS.edu', 'Csanchez5@nshs.edu', 'kcendagort@NSHS.edu', 'echeng3@NSHS.edu', 'jchung6@NSHS.edu', 'Ecotter@NSHS.edu', 'dlegodais@nshs.edu', 'Rdzenis@NSHS.edu', 'nevans2@nshs.edu', 'Tfairbaugh@nshs.edu', 'nfoy2@NSHS.edu', 'Agillenhur@NSHS.edu', 'Cgraviano@nshs.edu', 'Cgutierre2@nshs.edu', 'Chidalgo@NSHS.edu', 'khowell3@NSHS.edu', 'Khughes4@NSHS.edu', 'Cioannou1@nshs.edu', 'tjenulis1@NSHS.edu', 'Hjohnston@NSHS.edu', 'ljones11@NSHS.edu', 'Mkusko@NSHS.edu', 'Glouie@NSHS.edu', 'mmcder@nshs.edu', 'lmcdonald7@NSHS.edu', 'Mmcdonnel2@nshs.edu', 'smonosova1@NSHS.edu', 'jmulkeen@NSHS.edu', 'Jpassarel1@NSHS.edu', 'CQueliza@NSHS.edu', 'bredman1@NSHS.edu', 'Mricardo@nshs.edu', 'Brugova@nshs.edu', 'Kstrom@NSHS.edu', 'asee@NSHS.edu', 'Yshchapina@nshs.edu', 'ksheppard3@NSHS.edu', 'kshields2@NSHS.edu', 'Hsilva1@nshs.edu', 'Lsimeone@NSHS.edu', 'astallone@NSHS.edu', 'Etam@nshs.edu', 'cvarghese2@nshs.edu', 'svarughes7@NSHS.edu', 'gbalsamo@nshs.edu', 'mbailey4@nshs.edu', 'nkoentje@nshs.edu', 'mlacourte@nshs.edu', 'kurban@nshs.edu', 'Kweeks@nshs.edu', 'ccervone@nshs.edu', 'jchotowicky@nshs.edu', 'cciuffo@nshs.edu', 'Cdipuma@nshs.edu', 'eschilling@nshs.edu', 'edreisbach@nshs.edu', 'CHidalgo1@nshs.edu', 'gabrielle.amoroso@hotmail.com', 'crystal.rhodes@lpnt.net', 'smhurley43@gmail.com', 'sbelcher0009@email.vccs.edu', 'julie.matthews8@gmail.com', 'Lauren.Jones@LPNT.net', 'actracey@hotmail.com', 'miclackey@yahoo.com', 'kedwardsuncg@gmail.com', 'MELANIE24054@YAHOO.COM', 'kmpottle@aol.com', 'donna_menefee@yahoo.com', 'lajunna_ab@yahoo.com', 'scott.agee@lpnt.net', 'boothbabies3@yahoo.com', 'alexandriahagwood@gmail.com', 'mak2558@gmail.com', 'megbailey14@yahoo.com', 'dee@pregcc.com', 'kellymccorquodale@gmail.com', 'Vermont1.802@gmail.com', 'humiston_emily@yahoo.com', 'joeboyatt@yahoo.com', 'kathy4889@gmail.com', 'argabrightland@live.com', 'KEISHASCALES1@GMAIL.COM', 'c_bartley214@yahoo.com', 'missroland@gmail.com', 'purplehorsejamacia@yahoo.com', 'shnewman@comcast.net', 'terrichand@yahoo.com', 'pastorbattle1@yahoo.com', 'cherylaemery@gmail.com', 'kristen_rebecca@hotmail.com', 'shortie8907@gmail.com', 'stvyray@yahoo.com', 'carla.soyars@yahoo.com', 'venee.mayo@yahoo.com', 'housecallswithholly@counsellor.com', 'mike1inniss@gmail.com', 'heather.keatts1@gmail.com', 'stocktonmartin@gmail.com', 'kklebb7@gmail.com', 'suewat@gwmail.gwu.edu', 'JACKIESHAFFER907@GMAIL.COM', 'rsmith91@vt.edu', 'KARYNAJONES@YAHOO.COM', 'michael.chambers90@yahoo.com', 'geward@charter.net', 'mildred.owings@gmail.com', 'awttaylor@yahoo.com', 'RobinWalker0803@Gmail.Com', 'kimyrn92@gmail.com', 'sdb2010@email.vccs.edu', 'halfdznkids@yahoo.com', 'w.debbie95@yahoo.com', 'elizabeth.spagnoli@lpnt.net', 'Laura.Campbell@LPNT.net', 'Christin.Thompson@lpnt.net', 'Sandra.Avery@lpnt.net', 'Darrell.Whitenack@lpnt.net', 'Stephanie.Jenkins@lpnt.net', 'Amity.Walton@lpnt.net', 'Clesta.Mitchell@lpnt.net', 'Coty.Mullins@lpnt.net', 'April.Adkins@lpnt.net', 'Amy.Thomas@lpnt.net', 'Shawn.Willmott@lpnt.net', 'Allie.Barnett@lpnt.net', 'Ashley.Rose@lpnt.net', 'Shaundra.Call@lpnt.net', 'etzelg@yahoo.com', 'meghanlane1@outlook.com', 'erikaellis1@hotmail.com', 'ballison19@yahoo.com', 'tettie96@hotmail.com', 'brittany_polk@ymail.com', 'belchersk3@gmail.com', 'cari.lea@live.com', 'mcrobinson44@yahoo.com', 'amy.helton@lpnt.net', 'lisa.McElroy@lpnt.net', 'sylvia.potts@lpnt.net', 'lindsey.buchanan@lpnt.net', 'allison.huckaba@lpnt.net', 'gwen.davis@lpnt.net', 'deanna.mclain@lpnt.net', 'amy.smith@lpnt.net', 'christi.hall@lpnt.net', 'katrina.mcmurtry@lpnt.net', 'timothy.phelps@lpnt.net', 'brittney.white@lpnt.net', 'hagen.arevalo@lpnt.net', 'alissa.martin@lpnt.net', 'sheena.garrett1@lpnt.net', 'nicole.flynt@lpnt.net', 'elizha.burdette@lpnt.net', 'julia.coleman@lpnt.net', 'james.niedergeses@lpnt.net', 'savannah.gordon@lpnt.net', 'megan.brown@lpnt.net', 'christina.douthit@lpnt.net', 'katie.nave@lpnt.net', 'kaila.clark@lpnt.net', 'travis.richardson@lpnt.net', 'lora.rohling@lpnt.net', 'patricia.fowler@lpnt.net', 'sharon.mendenhall@lpnt.net', 'mylisa.shedd@lpnt.net', 'wendy.locke@lpnt.net', 'brook.young@lpnt.net', 'christina.rodriguez@lpnt.net', 'ashley.campbell@lpnt.net', 'rhonda.king@lpnt.net', 'candi.tarpley@lpnt.net', 'subbarao.daggubati@apogeephysicians.com', 'mailto:saranya.buppajarnatham@apogeephysicains.com')

									UNION

									SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login
									FROM OrganizationAdmins OA
									INNER JOIN Users U ON U.ID = OA.UserID
									INNER JOIN Licenses L ON L.OrganizationID = OA.OrganizationID
									INNER JOIN LicensePeriods LP ON LP.LicenseID = L.ID
									WHERE L.ProductID = 9
									AND EndDate > NOW()
									AND Login NOT LIKE '%apex%'
									AND LMS = 'N'
									
									UNION 
									
									SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login
									FROM (
										SELECT U.ID, U.FirstName, U.LastName, U.Login, U.DepartmentID, U.LMS
										FROM Users U INNER JOIN DepartmentAdmins DA ON DA.UserID = U.ID
									) AS U
									INNER JOIN Departments D ON D.ID = U.DepartmentID
									INNER JOIN Organizations O ON O.ID = D.OrganizationID
									INNER JOIN Licenses L ON L.OrganizationID = O.ID
									INNER JOIN LicensePeriods LP ON LP.LicenseID = L.ID
									WHERE L.ProductID = 9
									AND EndDate > NOW()
									AND Login NOT LIKE '%apex%'
									AND LMS = 'N'
									");


				// dd($users);
					
				foreach($users as $user)
				{	
					Mail::send('emails.impulse2TQ.careminderEmail',['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('imPULSE 2.0 Test Question Analysis and Adjustments');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'impulsetq');

			        });
				}

				return redirect('/email')->with('message','test email successfully sent'); 
				break;















			case 'CAhemiAdmins':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					Mail::queue('emails.hemi2.base','emails.hemi2.Current_Hemi_Admins_PlainTextEmail', ['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('Canadian Hemispheres 2.0 coming soon!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'cahemi2');

			        });
				}
				return redirect('/email')->with('message','hemiAdmins email successfully sent'); 
			case 'hemiAdminsReminder':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					Mail::queue('emails.hemi2.careminderEmail',['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('Canadian Hemispheres 2.0 is here!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'cahemi2');

			        });
				}
				return redirect('/email')->with('message','hemiAdmins email successfully sent'); 
			case 'hemiStore':
				$users = DB::select("SELECT DISTINCT(U.Login), U.Name FROM (SELECT ID, CONCAT(FirstName, ' ', LastName) AS Name, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				// SELECT DISTINCT(U.Login) FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				dd($users);
				foreach($users as $user)
				{					
					Mail::queue(['emails.hemi2.base','emails.hemi2.Current_Hemi_Store_PlainTextEmail'], ['image' => 'Current_Hemi_Store'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('Hemispheres 2.0 coming soon!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
			    }
				return redirect('/email')->with('message','hemiStore email successfully sent'); 
			case 'expCAHemiAdmins':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.EndDate < NOW() AND L.OrganizationID <> 2 AND L.OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.EndDate < NOW() AND L.OrganizationID <> 2 AND L.OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N')
				// dd($users);
				foreach($users as $user)
				{	
					Mail::queue(['emails.hemi2.base','emails.hemi2.Expired_Hemi_Admins_PlainTextEmail'], ['image' => 'Expired_CAHemi_Admins'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Canadian Hemispheres 2.0, Just Released!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'cahemi2');

			        });
			    }
				return redirect('/email')->with('message','expHemiAdmins email successfully sent'); 
			case 'expHemiStore':
				$users = DB::select("SELECT DISTINCT(U.Login), U.Name FROM (SELECT ID, CONCAT(FirstName, ' ', LastName) AS Name, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE L.ProductID = 2 AND LP.EndDate < NOW() AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT U.ID FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() ) AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				// SELECT DISTINCT(U.Login) FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE L.ProductID = 2 AND LP.EndDate < NOW() AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT U.ID FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() ) AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					Mail::queue(['emails.hemi2.base','emails.hemi2.Expired_Hemi_Store_PlainTextEmail'], ['image' => 'Expired_Hemi_Store'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Hemispheres 2.0, Just Released!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
			    }
				return redirect('/email')->with('message','expHemiStore email successfully sent'); 
			case 'nonHemiAdmins':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N')
				foreach($users as $user)
				{	
					Mail::queue(['emails.hemi2.base','emails.hemi2.Non_Hemi_Admins_PlainTextEmail'], ['image' => 'Non_Hemi_Admins'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Stroke Education from Apex Innovations, Hemispheres 2.0, Just Released!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
			    }
				return redirect('/email')->with('message','nonHemiAdmins email successfully sent'); 
			case 'nonHemiStore':
				$users = DB::select("SELECT DISTINCT(U.Login), U.Name FROM (SELECT ID, Login, CONCAT(FirstName, ' ', LastName) AS Name FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT U.ID FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE ProductID = 2 ) AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				foreach($users as $user)
				{	
				// SELECT DISTINCT(U.Login) FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT U.ID FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE ProductID = 2 ) AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
					Mail::queue(['emails.hemi2.base','emails.hemi2.Non_Hemi_Store_PlainTextEmail'], ['image' => 'Non_Hemi_Store'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Stroke Education from Apex Innovations, Hemispheres 2.0, Just Released!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
					error_log('win');
			    }
				return redirect('/email')->with('message','nonHemiStore email successfully sent'); 
			case 'canadianHemi':
				$users = DB::select("SELECT U.Login, CONCAT(U.Firstname, ' ' , U.LastName) AS Name FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				 // dd($users);
				foreach($users as $user)
				{	
						 
					Mail::queue('emails.hemi2.canadianHemi',['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Canadian Stroke Education from Apex Innovations, Canadian Hemispheres 2.0 COMING SOON!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
				}
				return redirect('/email')->with('message','canadianHemi email successfully sent'); 
			default:
				return redirect('/email')->with('errors','No email or invalid email specified');
		} 
	}	
}
