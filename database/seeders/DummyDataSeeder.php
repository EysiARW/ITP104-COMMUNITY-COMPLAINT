<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Complaint;
use App\Models\ComplaintAssignment;
use App\Models\ComplaintCategory;
use App\Models\ComplaintStatus;
use App\Models\Department;
use App\Models\Investigation;
use App\Models\PriorityLevel;
use App\Models\ReportType;
use App\Models\Resolution;
use App\Models\StatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = 1;
        $complainantRole = 2;
        $now = Carbon::now();

        // -------------------------------------------------------------
        // 1. DEPARTMENT COMMITTEE ROSTERS
        // -------------------------------------------------------------
        $staffMembers = [
            // Barangay Captain Office (1)
            [
                'email' => 'capt.banlican@barangay.local',
                'first_name' => 'Rodrigo',
                'last_name' => 'Banlican',
                'role_id' => $adminRole,
                'department_id' => 1,
                'committee_position' => 'Punong Barangay / Captain',
                'contact_number' => '09171110001',
                'address' => 'Purok 1, Banlic, Cabuyao',
            ],
            [
                'email' => 'sec.villanueva@barangay.local',
                'first_name' => 'Marites',
                'last_name' => 'Villanueva',
                'role_id' => $adminRole,
                'department_id' => 1,
                'committee_position' => 'Barangay Secretary',
                'contact_number' => '09171110002',
                'address' => 'Purok 2, Banlic, Cabuyao',
            ],
            [
                'email' => 'admin.dimayuga@barangay.local',
                'first_name' => 'Carlos',
                'last_name' => 'Dimayuga',
                'role_id' => $adminRole,
                'department_id' => 1,
                'committee_position' => 'Barangay Administrator',
                'contact_number' => '09171110003',
                'address' => 'Purok 3, Banlic, Cabuyao',
            ],

            // Peace and Order Committee (2)
            [
                'email' => 'kag.santos@barangay.local',
                'first_name' => 'Fernando',
                'last_name' => 'Santos',
                'role_id' => $adminRole,
                'department_id' => 2,
                'committee_position' => 'Committee Chairman / Security Officer',
                'contact_number' => '09172220001',
                'address' => 'Purok 1, Banlic, Cabuyao',
            ],
            [
                'email' => 'exo.dalisay@barangay.local',
                'first_name' => 'Ricardo',
                'last_name' => 'Dalisay',
                'role_id' => $adminRole,
                'department_id' => 2,
                'committee_position' => 'Chief Tanod / Field Operations Officer',
                'contact_number' => '09172220002',
                'address' => 'Purok 4, Banlic, Cabuyao',
            ],
            [
                'email' => 'tanod.bautista@barangay.local',
                'first_name' => 'Marlon',
                'last_name' => 'Bautista',
                'role_id' => $adminRole,
                'department_id' => 2,
                'committee_position' => 'Patrol Officer / Investigator',
                'contact_number' => '09172220003',
                'address' => 'Purok 2, Banlic, Cabuyao',
            ],
            [
                'email' => 'tanod.reyes@barangay.local',
                'first_name' => 'Noel',
                'last_name' => 'Reyes',
                'role_id' => $adminRole,
                'department_id' => 2,
                'committee_position' => 'Night Shift Team Leader',
                'contact_number' => '09172220004',
                'address' => 'Purok 5, Banlic, Cabuyao',
            ],

            // Health and Sanitation Committee (3)
            [
                'email' => 'kag.soriano@barangay.local',
                'first_name' => 'Elena',
                'last_name' => 'Soriano',
                'role_id' => $adminRole,
                'department_id' => 3,
                'committee_position' => 'Committee Chairperson - Health',
                'contact_number' => '09173330001',
                'address' => 'Purok 3, Banlic, Cabuyao',
            ],
            [
                'email' => 'dr.aquino@barangay.local',
                'first_name' => 'Maria Clara',
                'last_name' => 'Aquino',
                'role_id' => $adminRole,
                'department_id' => 3,
                'committee_position' => 'Health Center Physician',
                'contact_number' => '09173330002',
                'address' => 'Purok 1, Banlic, Cabuyao',
            ],
            [
                'email' => 'sanitation.perez@barangay.local',
                'first_name' => 'Gloria',
                'last_name' => 'Perez',
                'role_id' => $adminRole,
                'department_id' => 3,
                'committee_position' => 'Sanitation & Waste Inspector',
                'contact_number' => '09173330003',
                'address' => 'Purok 2, Banlic, Cabuyao',
            ],
            [
                'email' => 'nurse.manalo@barangay.local',
                'first_name' => 'Jocelyn',
                'last_name' => 'Manalo',
                'role_id' => $adminRole,
                'department_id' => 3,
                'committee_position' => 'Barangay Nutrition Scholar / Nurse',
                'contact_number' => '09173330004',
                'address' => 'Purok 4, Banlic, Cabuyao',
            ],

            // Infrastructure Committee (4)
            [
                'email' => 'engr.delacruz@barangay.local',
                'first_name' => 'Juan',
                'last_name' => 'De La Cruz',
                'role_id' => $adminRole,
                'department_id' => 4,
                'committee_position' => 'Committee Chairman / Lead Engineer',
                'contact_number' => '09174440001',
                'address' => 'Purok 1, Banlic, Cabuyao',
            ],
            [
                'email' => 'kag.mendoza@barangay.local',
                'first_name' => 'Roberto',
                'last_name' => 'Mendoza',
                'role_id' => $adminRole,
                'department_id' => 4,
                'committee_position' => 'Vice Chairman - Public Works',
                'contact_number' => '09174440002',
                'address' => 'Purok 2, Banlic, Cabuyao',
            ],
            [
                'email' => 'dante.alcantara@barangay.local',
                'first_name' => 'Dante',
                'last_name' => 'Alcantara',
                'role_id' => $adminRole,
                'department_id' => 4,
                'committee_position' => 'Roads & Drainage Maintenance Lead',
                'contact_number' => '09174440003',
                'address' => 'Purok 3, Banlic, Cabuyao',
            ],
            [
                'email' => 'arnel.ramos@barangay.local',
                'first_name' => 'Arnel',
                'last_name' => 'Ramos',
                'role_id' => $adminRole,
                'department_id' => 4,
                'committee_position' => 'Streetlights & Electrical Inspector',
                'contact_number' => '09174440004',
                'address' => 'Purok 5, Banlic, Cabuyao',
            ],

            // Lupon Tagapamayapa (5)
            [
                'email' => 'atty.garcia@barangay.local',
                'first_name' => 'Ramon',
                'last_name' => 'Garcia',
                'role_id' => $adminRole,
                'department_id' => 5,
                'committee_position' => 'Lupon Secretary / Senior Conciliator',
                'contact_number' => '09175550001',
                'address' => 'Purok 3, Banlic, Cabuyao',
            ],
            [
                'email' => 'remedios.tan@barangay.local',
                'first_name' => 'Remedios',
                'last_name' => 'Tan',
                'role_id' => $adminRole,
                'department_id' => 5,
                'committee_position' => 'Mediation Officer / Lupon Member',
                'contact_number' => '09175550002',
                'address' => 'Purok 4, Banlic, Cabuyao',
            ],
            [
                'email' => 'benjamin.castro@barangay.local',
                'first_name' => 'Benjamin',
                'last_name' => 'Castro',
                'role_id' => $adminRole,
                'department_id' => 5,
                'committee_position' => 'Community Peace Conciliator',
                'contact_number' => '09175550003',
                'address' => 'Purok 5, Banlic, Cabuyao',
            ],

            // Women and Children Desk (6)
            [
                'email' => 'kag.morales@barangay.local',
                'first_name' => 'Teresa',
                'last_name' => 'Morales',
                'role_id' => $adminRole,
                'department_id' => 6,
                'committee_position' => 'VAWC Desk Head / Chairperson',
                'contact_number' => '09176660001',
                'address' => 'Purok 2, Banlic, Cabuyao',
            ],
            [
                'email' => 'spo1.diaz@barangay.local',
                'first_name' => 'Cristina',
                'last_name' => 'Diaz',
                'role_id' => $adminRole,
                'department_id' => 6,
                'committee_position' => 'Women & Child Protection Officer',
                'contact_number' => '09176660002',
                'address' => 'Purok 1, Banlic, Cabuyao',
            ],
            [
                'email' => 'analyn.cruz@barangay.local',
                'first_name' => 'Analyn',
                'last_name' => 'Cruz',
                'role_id' => $adminRole,
                'department_id' => 6,
                'committee_position' => 'Social Welfare & Family Counselor',
                'contact_number' => '09176660003',
                'address' => 'Purok 4, Banlic, Cabuyao',
            ],
        ];

        foreach ($staffMembers as $staff) {
            User::updateOrCreate(
                ['email' => $staff['email']],
                array_merge($staff, [
                    'password_hash' => Hash::make('Staff@12345'),
                    'is_active' => true,
                    'verification_status' => User::STATUS_APPROVED,
                    'verified_at' => $now,
                    'verified_by' => 1,
                ])
            );
        }

        // -------------------------------------------------------------
        // 2. RESIDENT COMPLAINANTS
        // -------------------------------------------------------------
        $residents = [
            [
                'email' => 'carmela.reyes@resident.local',
                'first_name' => 'Carmela',
                'last_name' => 'Reyes',
                'contact_number' => '09181112222',
                'address' => 'Purok 1, Banlic, Cabuyao',
                'area_id' => 1,
            ],
            [
                'email' => 'marco.villanueva@resident.local',
                'first_name' => 'Marco',
                'last_name' => 'Villanueva',
                'contact_number' => '09183334444',
                'address' => 'Purok 3, Banlic, Cabuyao',
                'area_id' => 3,
            ],
            [
                'email' => 'grace.santos@resident.local',
                'first_name' => 'Grace',
                'last_name' => 'Santos',
                'contact_number' => '09185556666',
                'address' => 'Purok 4, Banlic, Cabuyao',
                'area_id' => 4,
            ],
            [
                'email' => 'eduardo.gomez@resident.local',
                'first_name' => 'Eduardo',
                'last_name' => 'Gomez',
                'contact_number' => '09187778888',
                'address' => 'Purok 5, Banlic, Cabuyao',
                'area_id' => 5,
            ],
        ];

        $residentUsers = [];
        // include existing users if any
        $existingResidents = User::where('role_id', $complainantRole)->get();
        foreach ($existingResidents as $er) {
            $residentUsers[] = $er;
        }

        foreach ($residents as $r) {
            $user = User::updateOrCreate(
                ['email' => $r['email']],
                array_merge($r, [
                    'role_id' => $complainantRole,
                    'password_hash' => Hash::make('Resident@12345'),
                    'is_active' => true,
                    'verification_status' => User::STATUS_APPROVED,
                    'verified_at' => $now,
                    'verified_by' => 1,
                ])
            );
            $residentUsers[] = $user;
        }

        // Helper maps
        $categories = ComplaintCategory::pluck('category_id', 'category_name')->all();
        $statuses = ComplaintStatus::pluck('status_id', 'status_name')->all();
        $priorities = PriorityLevel::pluck('priority_id', 'priority_name')->all();
        $reportTypes = ReportType::pluck('report_type_id', 'type_name')->all();
        $areas = Area::pluck('area_id', 'area_name')->all();

        // -------------------------------------------------------------
        // 3. DUMMY COMPLAINTS & INCIDENT REPORTS
        // -------------------------------------------------------------
        $complaintDefinitions = [
            [
                'title' => 'Broken Streetlights along Purok 2 Alleyway',
                'description' => 'Three consecutive lampposts along Alley 4 have been unlit for almost two weeks, causing safety concerns for students and workers coming home late.',
                'location_details' => 'Alley 4, near Purok 2 Outpost',
                'category' => 'Road / Infrastructure',
                'priority' => 'Medium',
                'report_type' => 'Concern',
                'area' => 'Purok 2',
                'status' => 'Assigned',
                'assigned_to_email' => 'arnel.ramos@barangay.local',
                'assign_remarks' => 'Assigned to Electrical Inspector for lamp fixture and breaker replacement.',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Deep Pothole Causing Motorcycle Accidents near Purok 3 Elementary School',
                'description' => 'A large, deep pothole opened up in the middle of the road after recent flash floods. Several motorcycles and tricycles have slipped while avoiding oncoming traffic.',
                'location_details' => 'Main Road fronting Banlic Elementary Gate 2, Purok 3',
                'category' => 'Road / Infrastructure',
                'priority' => 'Urgent',
                'report_type' => 'Complaint',
                'area' => 'Purok 3',
                'status' => 'In Progress',
                'assigned_to_email' => 'engr.delacruz@barangay.local',
                'assign_remarks' => 'Assigned to Infrastructure Committee Head. Dispatched cold mix asphalt patch crew.',
                'investigation_findings' => 'Site inspected at 9:30 AM. Culvert underneath road has minor seepage causing asphalt sinking. Patch repair ongoing; culvert scheduled for permanent concrete box casing next month.',
                'investigation_action' => 'Temporary hazard barricades placed; cold asphalt compacted to level road surface immediately.',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Collapsed Drainage Canal Culvert Causing Overflow During Heavy Rain',
                'description' => 'The concrete wall of the open canal collapsed and blocks water flow, causing dirty runoff to submerge residential driveways.',
                'location_details' => 'Sitio Riverside, Purok 1 canal junction',
                'category' => 'Road / Infrastructure',
                'priority' => 'High',
                'report_type' => 'Incident Report',
                'area' => 'Purok 1',
                'status' => 'Pending',
                'created_at' => Carbon::now()->subHours(18),
            ],
            [
                'title' => 'Excessive Midnight Videoke and Loud Disturbance Beyond Curfew Hours',
                'description' => 'Neighbor continuously holds loud outdoor karaoke sessions past 1:00 AM on weekdays despite verbal polite reminders.',
                'location_details' => 'Block 4 Lot 12, Purok 4',
                'category' => 'Noise Disturbance',
                'priority' => 'Medium',
                'report_type' => 'Complaint',
                'area' => 'Purok 4',
                'status' => 'Resolved',
                'assigned_to_email' => 'tanod.bautista@barangay.local',
                'assign_remarks' => 'Assigned to Duty Tanod for curfew noise enforcement.',
                'investigation_findings' => 'Responded on site. Household owner acknowledged violation and powered down equipment.',
                'investigation_action' => 'Official barangay citation letter served; owner signed agreement to abide by 10:00 PM quiet hours.',
                'resolution_details' => 'Case resolved amicably. Complainant confirmed disturbance ceased without further incidents.',
                'created_at' => Carbon::now()->subDays(14),
                'resolved_at' => Carbon::now()->subDays(12),
            ],
            [
                'title' => 'Unruly Youth Group Loitering and Drinking Along Purok 5 Covered Court',
                'description' => 'Group of non-resident youths drinking alcohol and leaving broken bottles around the basketball court premises after midnight.',
                'location_details' => 'Purok 5 Covered Court bleachers',
                'category' => 'Peace and Order',
                'priority' => 'High',
                'report_type' => 'Incident Report',
                'area' => 'Purok 5',
                'status' => 'In Progress',
                'assigned_to_email' => 'exo.dalisay@barangay.local',
                'assign_remarks' => 'Assigned to Chief Tanod to increase roving patrol frequency.',
                'investigation_findings' => 'Inspected court at 11:30 PM. Group dispersed; bottle fragments cleaned up by tanod team.',
                'investigation_action' => 'Court lights now scheduled for curfew shutoff at 10:30 PM; hourly night tanod logbook placed on site.',
                'created_at' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'Illegal Garbage Dumping Along Irrigation Canal Behind Purok 2',
                'description' => 'Unidentified residents dumping plastic bags of rotting food waste and animal carcasses into the canal, emitting foul odor.',
                'location_details' => 'Irrigation dike road, boundary of Purok 2',
                'category' => 'Garbage / Sanitation',
                'priority' => 'High',
                'report_type' => 'Complaint',
                'area' => 'Purok 2',
                'status' => 'Assigned',
                'assigned_to_email' => 'sanitation.perez@barangay.local',
                'assign_remarks' => 'Assigned to Sanitation Inspector to conduct ocular check and coordinate CENRO collection.',
                'created_at' => Carbon::now()->subDays(6),
            ],
            [
                'title' => 'Accumulation of Uncollected Segregated Trash Bags for Over a Week',
                'description' => 'Garbage collector truck skipped our street for two cycles. Stray cats and dogs are tearing bags open.',
                'location_details' => 'Sampaguita St., Purok 3',
                'category' => 'Garbage / Sanitation',
                'priority' => 'Medium',
                'report_type' => 'Complaint',
                'area' => 'Purok 3',
                'status' => 'Resolved',
                'assigned_to_email' => 'kag.soriano@barangay.local',
                'assign_remarks' => 'Assigned to Health & Sanitation Chairperson.',
                'resolution_details' => 'Coordinated with City Waste Management Office. Extra hauler truck dispatched. Backlog cleared.',
                'created_at' => Carbon::now()->subDays(20),
                'resolved_at' => Carbon::now()->subDays(18),
            ],
            [
                'title' => 'Stray Dogs Chasing Cyclists and Pedestrians Along Purok 4 Main Road',
                'description' => 'Pack of 5 untagged stray dogs aggressively barking and lunging at passing delivery riders and children walking to school.',
                'location_details' => 'Near Purok 4 Bakery junction',
                'category' => 'Animal Concerns',
                'priority' => 'Medium',
                'report_type' => 'Concern',
                'area' => 'Purok 4',
                'status' => 'Under Review',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Stagnant Water Puddle Creating Mosquito Breeding Ground Near Daycare',
                'description' => 'Vacant lot beside daycare has water accumulation with visible mosquito larvae. Parents are concerned about dengue outbreaks.',
                'location_details' => 'Purok 1 Daycare Center perimeter',
                'category' => 'Health and Safety',
                'priority' => 'High',
                'report_type' => 'Concern',
                'area' => 'Purok 1',
                'status' => 'In Progress',
                'assigned_to_email' => 'nurse.manalo@barangay.local',
                'assign_remarks' => 'Assigned to Nutrition Scholar/Nurse for larvicide and fogging coordination.',
                'investigation_findings' => 'Water sample positive for Aedes larvae. Coordinated with City Health Office for misting.',
                'investigation_action' => 'Applied abate granules in stagnant pools; issued cleanup notice to private lot owner.',
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'title' => 'Boundary Fence Dispute Between Neighbors on Alley 3',
                'description' => 'Neighbor erected a concrete post encroaching approximately 40 centimeters into our registered lot boundary.',
                'location_details' => 'Alley 3, Purok 2',
                'category' => 'Neighbor Dispute',
                'priority' => 'Low',
                'report_type' => 'Complaint',
                'area' => 'Purok 2',
                'status' => 'Under Review',
                'created_at' => Carbon::now()->subDays(8),
            ],
            [
                'title' => 'Disagreement Over Tree Branches Overhanging Adjacent Roof and Gutter',
                'description' => 'Neighbor refuses to trim large mango tree branches falling on our roof and clogging rainwater downspouts.',
                'location_details' => 'Camia St., Purok 5',
                'category' => 'Neighbor Dispute',
                'priority' => 'Low',
                'report_type' => 'Complaint',
                'area' => 'Purok 5',
                'status' => 'Resolved',
                'assigned_to_email' => 'remedios.tan@barangay.local',
                'assign_remarks' => 'Assigned to Lupon Mediation Officer.',
                'resolution_details' => 'Mediation session conducted at Barangay Hall on Sept 28. Neighbor agreed to trim branches jointly with barangay maintenance assistance.',
                'created_at' => Carbon::now()->subDays(35),
                'resolved_at' => Carbon::now()->subDays(28),
            ],
            [
                'title' => 'Domestic Verbal Disturbance and Family Altercation in Purok 3 Apartment',
                'description' => 'Heated shouting match and banging sounds from upstairs tenant causing fear among neighbors and minor children.',
                'location_details' => 'Apartment 2B, Purok 3',
                'category' => 'Domestic / VAWC',
                'priority' => 'Urgent',
                'report_type' => 'Incident Report',
                'area' => 'Purok 3',
                'status' => 'Assigned',
                'assigned_to_email' => 'kag.morales@barangay.local',
                'assign_remarks' => 'Assigned to VAWC Desk Chairperson & Child Protection Officer for counseling and safety interview.',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Stolen Brass Water Meter and Cut Pipes Outside Residence in Purok 1',
                'description' => 'Woke up at 6:00 AM to discover water gushing from severed supply line; Laguna Water meter was stolen overnight.',
                'location_details' => 'House 45, Purok 1',
                'category' => 'Theft / Property',
                'priority' => 'High',
                'report_type' => 'Incident Report',
                'area' => 'Purok 1',
                'status' => 'In Progress',
                'assigned_to_email' => 'tanod.bautista@barangay.local',
                'assign_remarks' => 'Assigned to Investigator to review neighborhood CCTV and assist with utility report.',
                'investigation_findings' => 'CCTV shows two suspects on bicycle at 2:45 AM. Footages forwarded to Cabuyao Police Station.',
                'investigation_action' => 'Issued barangay incident blotter certification for free meter replacement with Laguna Water.',
                'created_at' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'Damaged Basketball Board and Loose Steel Support at Purok 4 Sports Plaza',
                'description' => 'The backboard acrylic is cracked and the mounting bolt came loose, posing a risk of falling on players.',
                'location_details' => 'Purok 4 Plaza',
                'category' => 'Road / Infrastructure',
                'priority' => 'Low',
                'report_type' => 'Concern',
                'area' => 'Purok 4',
                'status' => 'Closed',
                'assigned_to_email' => 'dante.alcantara@barangay.local',
                'assign_remarks' => 'Assigned to Maintenance Lead.',
                'resolution_details' => 'Steel frame re-welded and reinforced. New marine-grade board installed. Facility reopened.',
                'created_at' => Carbon::now()->subDays(60),
                'resolved_at' => Carbon::now()->subDays(55),
            ],
            [
                'title' => 'Vague Commercial Payment Dispute Outside Barangay Jurisdiction',
                'description' => 'Requesting barangay intervention for online loan app harassment and credit card payment demand letter.',
                'location_details' => 'N/A',
                'category' => 'Others',
                'priority' => 'Low',
                'report_type' => 'Concern',
                'area' => 'Purok 1',
                'status' => 'Rejected',
                'created_at' => Carbon::now()->subDays(16),
            ],
            [
                'title' => 'Hazardous Low-Hanging Telecom Wire Across Street Near Purok 5 Terminal',
                'description' => 'Black fiber cable has dropped to just 6 feet above road level, catching delivery vans and jeepney roof racks.',
                'location_details' => 'Tricycle Terminal corner, Purok 5',
                'category' => 'Road / Infrastructure',
                'priority' => 'High',
                'report_type' => 'Incident Report',
                'area' => 'Purok 5',
                'status' => 'Pending',
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'title' => 'Suspected Dengue Case Clustering in Purok 3 Compound',
                'description' => 'Two children in neighboring households were hospitalized with confirmed dengue fever this week.',
                'location_details' => 'Purok 3 Compound, St. Jude Alley',
                'category' => 'Health and Safety',
                'priority' => 'Urgent',
                'report_type' => 'Incident Report',
                'area' => 'Purok 3',
                'status' => 'In Progress',
                'assigned_to_email' => 'dr.aquino@barangay.local',
                'assign_remarks' => 'Assigned to Health Center Doctor for contact tracing and vector surveillance.',
                'investigation_findings' => 'Barangay Health Workers conducted ocular visit. Identified roof gutters with standing water.',
                'investigation_action' => 'Community clean-up drive held Oct 6. Fogging performed across 200m radius.',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Continuous Motorcycle Revving and Open Pipe Exhaust at 1:30 AM',
                'description' => 'Modified exhaust motorcycle repeatedly revving along residential streets causing infant and elderly distress.',
                'location_details' => 'Purok 2 near Chapel',
                'category' => 'Noise Disturbance',
                'priority' => 'Low',
                'report_type' => 'Complaint',
                'area' => 'Purok 2',
                'status' => 'Pending',
                'created_at' => Carbon::now()->subHours(2),
            ],
        ];

        // Seed each complaint
        $adminUser = User::where('role_id', $adminRole)->first();
        $residentCount = count($residentUsers);

        foreach ($complaintDefinitions as $index => $def) {
            // Pick a resident complainant round-robin
            $complainant = $residentUsers[$index % $residentCount] ?? $adminUser;

            $catId = $categories[$def['category']] ?? 1;
            $statId = $statuses[$def['status']] ?? 1;
            $priId = $priorities[$def['priority']] ?? 2;
            $repId = $reportTypes[$def['report_type']] ?? 1;
            $areaId = $areas[$def['area']] ?? 1;

            $createdAt = $def['created_at'];

            // Check if already seeded by title
            $existing = Complaint::where('title', $def['title'])->first();
            if ($existing) {
                continue;
            }

            // Create complaint
            $complaint = Complaint::create([
                'reference_no' => 'TMP-' . uniqid(),
                'complainant_id' => $complainant->user_id,
                'report_type_id' => $repId,
                'category_id' => $catId,
                'priority_id' => $priId,
                'area_id' => $areaId,
                'status_id' => $statId,
                'title' => $def['title'],
                'description' => $def['description'],
                'location_details' => $def['location_details'] ?? null,
                'incident_date' => $createdAt->copy()->subHours(rand(1, 12)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Set official reference number
            $complaint->update([
                'reference_no' => sprintf('BRGY-%s-%05d', $createdAt->year, $complaint->complaint_id),
            ]);

            // 1. Initial Status History (Pending)
            StatusHistory::create([
                'complaint_id' => $complaint->complaint_id,
                'old_status_id' => null,
                'new_status_id' => $statuses['Pending'] ?? 1,
                'changed_by' => $complainant->user_id,
                'remarks' => 'Complaint submitted by resident.',
                'changed_at' => $createdAt,
            ]);

            // 2. Assignment (if assigned officer specified)
            if (!empty($def['assigned_to_email'])) {
                $officer = User::where('email', $def['assigned_to_email'])->first();
                if ($officer) {
                    ComplaintAssignment::create([
                        'complaint_id' => $complaint->complaint_id,
                        'assigned_to' => $officer->user_id,
                        'assigned_by' => $adminUser->user_id,
                        'remarks' => $def['assign_remarks'] ?? 'Assigned to relevant committee staff.',
                        'is_current' => true,
                        'assigned_at' => $createdAt->copy()->addHours(2),
                    ]);

                    StatusHistory::create([
                        'complaint_id' => $complaint->complaint_id,
                        'old_status_id' => $statuses['Pending'] ?? 1,
                        'new_status_id' => $statuses['Assigned'] ?? 3,
                        'changed_by' => $adminUser->user_id,
                        'remarks' => $def['assign_remarks'] ?? "Assigned to {$officer->full_name}.",
                        'changed_at' => $createdAt->copy()->addHours(2),
                    ]);
                }
            }

            // 3. Investigation (if specified)
            if (!empty($def['investigation_findings'])) {
                $investigator = !empty($def['assigned_to_email'])
                    ? User::where('email', $def['assigned_to_email'])->first()
                    : $adminUser;

                Investigation::create([
                    'complaint_id' => $complaint->complaint_id,
                    'investigator_id' => $investigator->user_id ?? $adminUser->user_id,
                    'investigation_date' => $createdAt->copy()->addHours(6),
                    'findings' => $def['investigation_findings'],
                    'actions_taken' => $def['investigation_action'] ?? null,
                    'created_at' => $createdAt->copy()->addHours(6),
                ]);

                StatusHistory::create([
                    'complaint_id' => $complaint->complaint_id,
                    'old_status_id' => $statuses['Assigned'] ?? 3,
                    'new_status_id' => $statuses['In Progress'] ?? 4,
                    'changed_by' => $investigator->user_id ?? $adminUser->user_id,
                    'remarks' => 'Ocular inspection performed. Investigation findings recorded.',
                    'changed_at' => $createdAt->copy()->addHours(6),
                ]);
            }

            // 4. Resolution (if resolved)
            if (!empty($def['resolution_details'])) {
                $resolvedAt = $def['resolved_at'] ?? $createdAt->copy()->addDays(2);
                $resolver = !empty($def['assigned_to_email'])
                    ? User::where('email', $def['assigned_to_email'])->first()
                    : $adminUser;

                Resolution::create([
                    'complaint_id' => $complaint->complaint_id,
                    'resolved_by' => $resolver->user_id ?? $adminUser->user_id,
                    'resolution_details' => $def['resolution_details'],
                    'resolved_at' => $resolvedAt,
                    'created_at' => $resolvedAt,
                ]);

                StatusHistory::create([
                    'complaint_id' => $complaint->complaint_id,
                    'old_status_id' => $statuses['In Progress'] ?? 4,
                    'new_status_id' => $statId,
                    'changed_by' => $resolver->user_id ?? $adminUser->user_id,
                    'remarks' => 'Case officially marked resolved.',
                    'changed_at' => $resolvedAt,
                ]);
            }

            // 5. Rejection (if rejected)
            if ($def['status'] === 'Rejected') {
                StatusHistory::create([
                    'complaint_id' => $complaint->complaint_id,
                    'old_status_id' => $statuses['Pending'] ?? 1,
                    'new_status_id' => $statuses['Rejected'] ?? 7,
                    'changed_by' => $adminUser->user_id,
                    'remarks' => 'Concern falls under private contractual dispute outside Barangay Banlic jurisdiction. Referred to PAO.',
                    'changed_at' => $createdAt->copy()->addHours(3),
                ]);
            }
        }
    }
}
