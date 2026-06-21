<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Notice;
use App\Models\InstituteEvent;
use App\Models\Blog;
use App\Models\GalleryAlbum;
use App\Models\StudentResource;
use App\Models\Stat;
use App\Models\Facility;
use App\Models\Achievement;
use App\Models\Faq;
use App\Models\Email;
use App\Models\Course;
use App\Models\CourseResult;
use App\Models\Bill;
use App\Models\HeroSlide;
use App\Models\SocialLink;
use App\Models\Admission;
use App\Models\ClassRoutine;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Stats
        Stat::create(['label' => 'Academic Departments', 'value' => '3', 'sort_order' => 1]);
        Stat::create(['label' => 'Experienced Faculty', 'value' => '86+', 'sort_order' => 2]);
        Stat::create(['label' => 'Modern Laboratories', 'value' => '24', 'sort_order' => 3]);
        Stat::create(['label' => 'Student Clubs', 'value' => '12', 'sort_order' => 4]);

        // Facilities
        Facility::create(['title' => 'Digital Library', 'description' => 'A quiet study environment with technical references, journals, and digital learning resources.', 'sort_order' => 1]);
        Facility::create(['title' => 'Computer Labs', 'description' => 'Well-equipped labs supporting programming, networking, CAD, and office automation practice.', 'sort_order' => 2]);
        Facility::create(['title' => 'Civil Workshop', 'description' => 'Surveying, materials testing, and drafting facilities for hands-on civil technology training.', 'sort_order' => 3]);
        Facility::create(['title' => 'Electrical Lab', 'description' => 'Electrical machines, power electronics, control systems, and instrumentation practice spaces.', 'sort_order' => 4]);

        // Achievements
        Achievement::create(['title' => 'National Skill Competition', 'description' => 'Students consistently represent CMPI in national technical skill competitions.', 'sort_order' => 1]);
        Achievement::create(['title' => 'Industry Linkage', 'description' => 'MoUs and internship pathways with leading local and national technical organizations.', 'sort_order' => 2]);
        Achievement::create(['title' => 'Community Service', 'description' => "Regular blood donation, tree plantation, and digital literacy initiatives across Cox's Bazar.", 'sort_order' => 3]);

        // FAQs
        Faq::create(['question' => 'Who is eligible for diploma admission?', 'answer' => 'Applicants must meet the current Bangladesh Technical Education Board eligibility criteria and submit required academic documents.', 'sort_order' => 1]);
        Faq::create(['question' => 'Does CMPI provide hostel facilities?', 'answer' => 'Hostel-related information is announced by the institute authority according to seat availability and government guidelines.', 'sort_order' => 2]);
        Faq::create(['question' => 'How can I collect a notice copy?', 'answer' => 'Notices can be downloaded from the Notice Board page or collected from the relevant administrative office with proper identification.', 'sort_order' => 3]);
        Faq::create(['question' => 'Are practical labs available for all departments?', 'answer' => 'Yes. CMPI maintains department-specific labs and workshops for Civil, CST, and Electrical Technology programs.', 'sort_order' => 4]);

        // Departments
        Department::create([
            'slug' => 'civil-technology',
            'title' => 'Civil Technology',
            'short_title' => 'Civil',
            'description' => 'Develops skilled technologists in surveying, drafting, construction management, and infrastructure maintenance.',
            'overview' => 'Civil Technology at CMPI blends classroom theory with practical laboratory work in surveying, construction materials, estimating, and CAD-based drafting.',
            'objectives' => ['Apply surveying and leveling principles in field projects.', 'Prepare structural and architectural drawings using modern drafting tools.', 'Understand construction materials, estimation, and project supervision.'],
            'labs' => ['Surveying Lab', 'Materials Testing Lab', 'AutoCAD Studio', 'Construction Practice Workshop'],
            'achievements' => ['Best project award in regional technical exhibition', 'Active participation in campus infrastructure planning'],
            'career_opportunities' => ['Site Supervisor', 'CAD Technician', 'Survey Assistant', 'Construction Estimator'],
        ]);

        Department::create([
            'slug' => 'computer-science-technology',
            'title' => 'Computer Science & Technology',
            'short_title' => 'CST',
            'description' => 'Focuses on programming, networking, database systems, web development, and emerging digital technologies.',
            'overview' => 'The Computer Science & Technology department provides industry-aligned training in software development, networking, database management, and ICT support.',
            'objectives' => ['Build software applications using modern programming practices.', 'Configure and troubleshoot computer networks.', 'Develop database-driven solutions for real-world problems.'],
            'labs' => ['Programming Lab', 'Networking Lab', 'Database Lab', 'Web Development Lab'],
            'achievements' => ['National programming contest participation', 'Student-led campus management mini projects'],
            'career_opportunities' => ['Software Developer', 'Network Technician', 'Database Assistant', 'ICT Support Officer'],
        ]);

        Department::create([
            'slug' => 'electrical-technology',
            'title' => 'Electrical Technology',
            'short_title' => 'Electrical',
            'description' => 'Covers electrical circuits, machines, power systems, electronics, and automation fundamentals.',
            'overview' => 'Electrical Technology trains students in installation, maintenance, testing, and troubleshooting of electrical systems.',
            'objectives' => ['Analyze electrical circuits and machine operations.', 'Perform safe electrical installation and maintenance tasks.', 'Understand renewable energy and automation fundamentals.'],
            'labs' => ['Electrical Machines Lab', 'Power Electronics Lab', 'Control Systems Lab', 'Basic Electronics Lab'],
            'achievements' => ['Solar energy awareness project', 'Technical demonstration at national polytechnic fair'],
            'career_opportunities' => ['Electrical Technician', 'Power Plant Assistant', 'Maintenance Supervisor', 'Automation Assistant'],
        ]);

        // Faculty
        Faculty::create(['name' => 'Md. Rafiqul Islam', 'designation' => 'Principal', 'department' => 'Administration', 'qualification' => 'M.Sc. in Engineering', 'email' => 'principal@cmpi.edu.bd', 'phone' => '+880 341 000101', 'specialization' => ['Institutional Leadership', 'Technical Education']]);
        Faculty::create(['name' => 'Engr. Nasrin Akter', 'designation' => 'Head of Department', 'department' => 'civil-technology', 'qualification' => 'B.Sc. in Civil Engineering', 'email' => 'nasrin.civil@cmpi.edu.bd', 'phone' => '+880 341 000102', 'specialization' => ['Structural Engineering', 'Surveying']]);
        Faculty::create(['name' => 'Md. Kamal Hossain', 'designation' => 'Lecturer', 'department' => 'civil-technology', 'qualification' => 'B.Sc. in Civil Engineering', 'email' => 'kamal.civil@cmpi.edu.bd', 'phone' => '+880 341 000103', 'specialization' => ['Construction Management', 'AutoCAD']]);
        Faculty::create(['name' => 'Tanjila Rahman', 'designation' => 'Head of Department', 'department' => 'computer-science-technology', 'qualification' => 'M.Sc. in CSE', 'email' => 'tanjila.cst@cmpi.edu.bd', 'phone' => '+880 341 000104', 'specialization' => ['Software Engineering', 'Database Systems']]);
        Faculty::create(['name' => 'Md. Shahriar Kabir', 'designation' => 'Lecturer', 'department' => 'computer-science-technology', 'qualification' => 'B.Sc. in CSE', 'email' => 'shahriar.cst@cmpi.edu.bd', 'phone' => '+880 341 000105', 'specialization' => ['Networking', 'Web Development']]);
        Faculty::create(['name' => 'Farhana Yeasmin', 'designation' => 'Instructor', 'department' => 'computer-science-technology', 'qualification' => 'M.Sc. in ICT', 'email' => 'farhana.cst@cmpi.edu.bd', 'phone' => '+880 341 000106', 'specialization' => ['Programming', 'Multimedia']]);
        Faculty::create(['name' => 'Engr. Mizanur Rahman', 'designation' => 'Head of Department', 'department' => 'electrical-technology', 'qualification' => 'B.Sc. in Electrical Engineering', 'email' => 'mizan.electrical@cmpi.edu.bd', 'phone' => '+880 341 000107', 'specialization' => ['Power Systems', 'Electrical Machines']]);
        Faculty::create(['name' => 'Sumaiya Islam', 'designation' => 'Lecturer', 'department' => 'electrical-technology', 'qualification' => 'B.Sc. in EEE', 'email' => 'sumaiya.electrical@cmpi.edu.bd', 'phone' => '+880 341 000108', 'specialization' => ['Electronics', 'Control Systems']]);

        // Notices
        Notice::create(['title' => 'Admission Application Open for 2026-2027 Session', 'category' => 'Admission', 'date' => '2026-06-10', 'summary' => 'Online and offline application procedures are now available for diploma admission.', 'details' => 'Applicants must submit academic transcripts, citizenship documents, passport-size photographs, and the prescribed application fee.', 'file_url' => '/notices/admission-2026.pdf']);
        Notice::create(['title' => 'Mid-Term Examination Routine Published', 'category' => 'Exam', 'date' => '2026-06-05', 'summary' => 'The mid-term examination routine for all departments has been published.', 'details' => 'Students are advised to collect the routine from the academic section.', 'file_url' => '/notices/midterm-routine.pdf']);
        Notice::create(['title' => 'Eid-ul-Adha Holiday Announcement', 'category' => 'Holiday', 'date' => '2026-05-28', 'summary' => 'The institute will remain closed for Eid-ul-Adha vacation as per government guidelines.', 'details' => 'Regular academic activities will resume according to the academic calendar.', 'file_url' => '/notices/eid-holiday.pdf']);
        Notice::create(['title' => 'Tender Notice for Laboratory Equipment', 'category' => 'Tender', 'date' => '2026-05-20', 'summary' => 'Quotations are invited from eligible suppliers for laboratory equipment procurement.', 'details' => 'Interested suppliers must submit sealed quotations with technical specifications.', 'file_url' => '/notices/tender-lab-equipment.pdf']);

        // Events
        InstituteEvent::create(['title' => 'Technical Career Fair 2026', 'date' => '2026-07-15', 'time' => '10:00 AM - 4:00 PM', 'venue' => 'Central Auditorium', 'category' => 'Academic', 'status' => 'Upcoming', 'summary' => 'Industry partners will showcase career pathways, internships, and technical training opportunities.', 'details' => 'Students from all departments are encouraged to bring resumes.']);
        InstituteEvent::create(['title' => 'National Technology Day Celebration', 'date' => '2026-10-28', 'time' => '9:00 AM - 1:00 PM', 'venue' => 'Campus Ground', 'category' => 'Cultural', 'status' => 'Upcoming', 'summary' => 'A day-long celebration featuring projects, cultural programs, and technical demonstrations.', 'details' => 'Departments will present student projects.']);
        InstituteEvent::create(['title' => 'Web Development Workshop', 'date' => '2026-06-22', 'time' => '2:00 PM - 5:00 PM', 'venue' => 'Computer Lab 2', 'category' => 'Workshop', 'status' => 'Upcoming', 'summary' => 'Hands-on workshop on responsive web development and deployment fundamentals.', 'details' => 'Participants will learn HTML, CSS, JavaScript, and deployment.']);
        InstituteEvent::create(['title' => 'Annual Sports Meet 2026', 'date' => '2026-04-18', 'time' => '8:00 AM - 5:00 PM', 'venue' => 'Campus Sports Field', 'category' => 'Sports', 'status' => 'Past', 'summary' => 'Inter-department sports competitions were held with enthusiastic student participation.', 'details' => 'Civil Technology secured the championship title.']);

        // Blogs
        Blog::create(['slug' => 'why-polytechnic-education-matters', 'title' => 'Why Polytechnic Education Matters for National Development', 'excerpt' => 'A practical look at how diploma engineers contribute to infrastructure, ICT, and industrial growth.', 'content' => 'Polytechnic education connects classroom learning with workplace-ready skills.', 'author' => 'Academic Cell', 'date' => '2026-06-01', 'category' => 'Academics', 'read_time' => '5 min read', 'related_ids' => ['blog-02', 'blog-03']]);
        Blog::create(['slug' => 'campus-life-at-cmpi', 'title' => 'Campus Life at CMPI: Learning Beyond the Classroom', 'excerpt' => 'Explore clubs, events, labs, and student support services that shape daily campus life.', 'content' => 'CMPI campus life combines academic rigor with co-curricular activities.', 'author' => 'Student Welfare', 'date' => '2026-05-18', 'category' => 'Campus', 'read_time' => '4 min read', 'related_ids' => ['blog-01', 'blog-03']]);
        Blog::create(['slug' => 'admission-guide-2026', 'title' => 'Admission Guide 2026: What Applicants Should Know', 'excerpt' => 'A concise checklist for documents, eligibility, fees, and important admission dates.', 'content' => 'Prospective students should review eligibility criteria and prepare academic transcripts.', 'author' => 'Admission Office', 'date' => '2026-05-10', 'category' => 'Admission', 'read_time' => '6 min read', 'related_ids' => ['blog-01', 'blog-02']]);

        // Gallery Albums
        GalleryAlbum::create(['title' => 'Campus Overview', 'count' => 24, 'description' => 'Academic buildings, central field, library, and common spaces.', 'accent' => 'from-emerald-700 to-emerald-500']);
        GalleryAlbum::create(['title' => 'Laboratories', 'count' => 36, 'description' => 'Civil, CST, and electrical laboratories with practical learning spaces.', 'accent' => 'from-amber-500 to-yellow-300']);
        GalleryAlbum::create(['title' => 'Student Activities', 'count' => 18, 'description' => 'Clubs, cultural programs, sports, and community initiatives.', 'accent' => 'from-slate-800 to-slate-600']);

        // Resources
        StudentResource::create(['title' => 'Academic Calendar 2026', 'type' => 'PDF', 'description' => 'Semester timeline, examination windows, holidays, and result publication schedule.', 'updated_at_date' => '2026-06-01']);
        StudentResource::create(['title' => 'Class Routine Template', 'type' => 'XLS', 'description' => 'Department-wise class routine template for students and teachers.', 'updated_at_date' => '2026-05-25']);
        StudentResource::create(['title' => 'Admission Application Form', 'type' => 'DOC', 'description' => 'Printable admission form for offline submission.', 'updated_at_date' => '2026-05-20']);
        StudentResource::create(['title' => 'Student Portal Login', 'type' => 'Link', 'description' => 'Access academic records, notices, and downloadable forms.', 'updated_at_date' => '2026-06-05']);

        // Users (students + admin)
        $rahim = User::create(['name' => 'Rahim Miah', 'email' => 'rahim.cst@cmpi.edu.bd', 'password' => Hash::make('rahim123'), 'department' => 'Computer Science & Technology', 'student_id' => 'CMPI-2023-0456', 'semester' => '4th', 'session' => '2023-2024', 'phone' => '+880 1XXX-XXXXXX', 'guardian' => 'Karim Miah (Father)', 'blood_group' => 'A+', 'address' => "Cox's Bazar, Bangladesh", 'admission_date' => '2023-06-15', 'role' => 'student']);
        $fatima = User::create(['name' => 'Fatima Khatun', 'email' => 'fatima.civil@cmpi.edu.bd', 'password' => Hash::make('fatima123'), 'department' => 'Civil Technology', 'student_id' => 'CMPI-2023-0312', 'semester' => '4th', 'session' => '2023-2024', 'phone' => '+880 1XXX-XXXXXX', 'guardian' => 'Abdul Kader (Father)', 'blood_group' => 'B+', 'address' => "Teknaf, Cox's Bazar", 'admission_date' => '2023-06-15', 'role' => 'student']);
        $arif = User::create(['name' => 'Arif Rahman', 'email' => 'arif.eee@cmpi.edu.bd', 'password' => Hash::make('arif123'), 'department' => 'Electrical Technology', 'student_id' => 'CMPI-2024-0108', 'semester' => '2nd', 'session' => '2024-2025', 'phone' => '+880 1XXX-XXXXXX', 'guardian' => 'Hasan Rahman (Father)', 'blood_group' => 'O+', 'address' => "Chakaria, Cox's Bazar", 'admission_date' => '2024-01-10', 'role' => 'student']);
        User::create(['name' => 'Admin User', 'email' => 'admin@cmpi.edu.bd', 'password' => Hash::make('admin123'), 'department' => 'Administration', 'student_id' => 'ADMIN-001', 'semester' => '-', 'session' => '-', 'phone' => '+880 341-000000', 'guardian' => '-', 'blood_group' => '-', 'address' => 'College Road, Cox\'s Bazar 4750', 'role' => 'admin']);

        // Courses for Rahim
        Course::create(['code' => 'CST-301', 'title' => 'Web Development', 'instructor' => 'Engr. Akter', 'user_id' => $rahim->id, 'progress' => 72, 'attendance' => '88%', 'next_class' => 'Mon 10:00 AM']);
        Course::create(['code' => 'CST-302', 'title' => 'Database Management', 'instructor' => 'Engr. Nasrin', 'user_id' => $rahim->id, 'progress' => 60, 'attendance' => '92%', 'next_class' => 'Tue 11:30 AM']);
        Course::create(['code' => 'CST-303', 'title' => 'Computer Networks', 'instructor' => 'Engr. Hossain', 'user_id' => $rahim->id, 'progress' => 84, 'attendance' => '95%', 'next_class' => 'Wed 09:00 AM']);

        // Course Results for Rahim
        CourseResult::create(['user_id' => $rahim->id, 'semester' => '3rd', 'sgpa' => 3.75, 'courses' => [['name' => 'Programming', 'grade' => 'A+', 'gp' => 4.0], ['name' => 'Mathematics', 'grade' => 'A', 'gp' => 3.75]]]);
        CourseResult::create(['user_id' => $rahim->id, 'semester' => '2nd', 'sgpa' => 3.60, 'courses' => [['name' => 'Electronics', 'grade' => 'A', 'gp' => 3.75], ['name' => 'English', 'grade' => 'A-', 'gp' => 3.50]]]);

        // Bills for Rahim
        Bill::create(['user_id' => $rahim->id, 'title' => 'Semester Tuition Fee', 'amount' => 12500, 'due' => '2026-07-15', 'status' => 'pending']);
        Bill::create(['user_id' => $rahim->id, 'title' => 'Lab Fee', 'amount' => 2000, 'due' => '2026-07-30', 'status' => 'pending']);
        Bill::create(['user_id' => $rahim->id, 'title' => 'Examination Fee', 'amount' => 1500, 'due' => '2026-08-10', 'status' => 'pending']);

        // Emails
        $emails = [
            ['from_email' => 'admin@cmpi.edu.bd', 'to_email' => 'rahim.cst@cmpi.edu.bd', 'subject' => 'Welcome to CMPI Student Email', 'preview' => 'Your institute email account has been created.', 'body' => "Dear Rahim,\n\nYour student email account rahim.cst@cmpi.edu.bd has been created.\n\nBest regards,\nCMPI Admin", 'date' => '2026-06-18', 'folder' => 'inbox', 'read' => false, 'starred' => true, 'label' => 'work'],
            ['from_email' => 'exam@cmpi.edu.bd', 'to_email' => 'rahim.cst@cmpi.edu.bd', 'subject' => 'Mid-term examination routine published', 'preview' => 'The mid-term exam routine is now available.', 'body' => "Dear Rahim Miah,\n\nThe mid-term examination routine for 4th semester CST has been published.\n\nBest regards,\nExam Controller", 'date' => '2026-06-17', 'folder' => 'inbox', 'read' => false, 'starred' => false, 'label' => 'urgent'],
            ['from_email' => 'tanjila.cst@cmpi.edu.bd', 'to_email' => 'rahim.cst@cmpi.edu.bd', 'subject' => 'Web Development assignment deadline', 'preview' => 'The deadline for the web dev project has been extended.', 'body' => "Dear Rahim,\n\nThe deadline for the responsive web development project has been extended to June 25th.\n\nBest,\nEngr. Tanjila Rahman", 'date' => '2026-06-16', 'folder' => 'inbox', 'read' => true, 'starred' => false, 'label' => 'work'],
            ['from_email' => 'lab@cmpi.edu.bd', 'to_email' => 'rahim.cst@cmpi.edu.bd', 'subject' => 'Database lab assignment 3', 'preview' => 'Complete SQL exercises for the database lab.', 'body' => "Dear Students,\n\nComplete the SQL join exercises for Database Lab Assignment 3. Deadline: June 20th.\n\nBest,\nLab Instructor", 'date' => '2026-06-15', 'folder' => 'inbox', 'read' => true, 'starred' => false, 'label' => 'work'],
            ['from_email' => 'rahim.cst@cmpi.edu.bd', 'to_email' => 'admin@cmpi.edu.bd', 'subject' => 'Leave application', 'preview' => 'I am requesting leave for...', 'body' => "Respected Sir,\n\nI am requesting leave for 2 days due to personal reasons.\n\nThank you.\nRahim Miah", 'date' => '2026-06-16', 'folder' => 'sent', 'read' => true, 'starred' => false, 'label' => 'personal'],
            ['from_email' => 'rahim.cst@cmpi.edu.bd', 'to_email' => 'tanjila.cst@cmpi.edu.bd', 'subject' => 'Project submission - React app', 'preview' => 'I have completed the web development project.', 'body' => "Dear Ma'am,\n\nI have completed the web development project.\n\nBest,\nRahim", 'date' => '2026-06-14', 'folder' => 'sent', 'read' => true, 'starred' => true, 'label' => 'work'],
            ['from_email' => 'admin@cmpi.edu.bd', 'to_email' => 'fatima.civil@cmpi.edu.bd', 'subject' => 'Welcome to CMPI Student Email', 'preview' => 'Your institute email account has been created.', 'body' => "Dear Fatima,\n\nYour student email account has been created.\n\nBest regards,\nCMPI Admin", 'date' => '2026-06-18', 'folder' => 'inbox', 'read' => false, 'starred' => false, 'label' => 'work'],
            ['from_email' => 'nasrin.civil@cmpi.edu.bd', 'to_email' => 'fatima.civil@cmpi.edu.bd', 'subject' => 'Surveying field work schedule', 'preview' => 'The surveying field work has been scheduled...', 'body' => "Dear Fatima,\n\nThe surveying field work for this week has been scheduled for Saturday.\n\nBest,\nEngr. Nasrin Akter", 'date' => '2026-06-17', 'folder' => 'inbox', 'read' => false, 'starred' => false, 'label' => 'urgent'],
            ['from_email' => 'admin@cmpi.edu.bd', 'to_email' => 'arif.eee@cmpi.edu.bd', 'subject' => 'Welcome to CMPI Student Email', 'preview' => 'Your institute email account has been created.', 'body' => "Dear Arif,\n\nYour student email account has been created.\n\nBest regards,\nCMPI Admin", 'date' => '2026-06-18', 'folder' => 'inbox', 'read' => false, 'starred' => false, 'label' => 'work'],
            ['from_email' => 'mizan.electrical@cmpi.edu.bd', 'to_email' => 'arif.eee@cmpi.edu.bd', 'subject' => 'Power electronics lab report', 'preview' => 'Submit your lab report on thyristor circuits...', 'body' => "Dear Arif,\n\nSubmit your lab report on thyristor circuits by next Monday.\n\nBest,\nEngr. Mizanur Rahman", 'date' => '2026-06-17', 'folder' => 'inbox', 'read' => false, 'starred' => false, 'label' => 'work'],
            ['from_email' => 'admin@cmpi.edu.bd', 'to_email' => 'all-students@cmpi.edu.bd', 'subject' => 'Eid-ul-Adha Holiday Announcement', 'preview' => 'The institute will remain closed for Eid-ul-Adha vacation.', 'body' => "Dear Students,\n\nThe institute will remain closed for Eid-ul-Adha vacation from June 28 to July 3, 2026.\n\nBest regards,\nAdministration", 'date' => '2026-06-15', 'folder' => 'inbox', 'read' => true, 'starred' => false, 'label' => null],
            ['from_email' => 'exam@cmpi.edu.bd', 'to_email' => 'all-students@cmpi.edu.bd', 'subject' => 'Result publication - 3rd semester', 'preview' => '3rd semester results have been published.', 'body' => "Dear Students,\n\nThe 3rd semester final examination results have been published.\n\nBest regards,\nExam Controller", 'date' => '2026-06-10', 'folder' => 'inbox', 'read' => true, 'starred' => false, 'label' => null],
        ];

        foreach ($emails as $email) {
            Email::create($email);
        }

        // Hero Slides
        HeroSlide::create([
            'eyebrow' => 'Welcome to CMPI',
            'title' => 'Cox\'s Bazar Model Polytechnic Institute',
            'description' => 'Empowering diploma engineers with practical skills and industry-ready training since 1985.',
            'cta_label' => 'Explore Programs',
            'cta_href' => '/academics',
            'panel_title' => 'Why CMPI?',
            'panel_description' => 'Hands-on technical education with experienced faculty, modern labs, and industry partnerships.',
            'stats' => [['label' => 'Departments', 'value' => '3'], ['label' => 'Faculty', 'value' => '86+']],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        HeroSlide::create([
            'eyebrow' => 'Admission Open 2026',
            'title' => 'Apply Now for Diploma Programs',
            'description' => 'Join Civil Technology, Computer Science & Technology, or Electrical Technology. Limited seats available.',
            'cta_label' => 'Apply Now',
            'cta_href' => '/admission',
            'secondary_label' => 'View Eligibility',
            'secondary_href' => '/academics',
            'panel_title' => '2026-2027 Session',
            'panel_description' => 'Online and offline applications accepted. Prepare academic transcripts, citizenship documents, and photographs.',
            'stats' => [['label' => 'Seats', 'value' => '180'], ['label' => 'Deadline', 'value' => 'Jul 31']],
            'sort_order' => 2,
            'is_active' => true,
        ]);
        HeroSlide::create([
            'eyebrow' => 'Campus Life',
            'title' => 'Learn Beyond the Classroom',
            'description' => 'Discover labs, workshops, clubs, and events that shape your polytechnic experience.',
            'cta_label' => 'View Gallery',
            'cta_href' => '/gallery',
            'panel_title' => 'Student Activities',
            'panel_description' => 'Technical clubs, sports events, cultural programs, and community service initiatives.',
            'stats' => [['label' => 'Clubs', 'value' => '12'], ['label' => 'Events/yr', 'value' => '20+']],
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Social Links
        SocialLink::create(['platform' => 'facebook', 'url' => 'https://facebook.com/cmpi.edu.bd', 'sort_order' => 1]);
        SocialLink::create(['platform' => 'youtube', 'url' => 'https://youtube.com/@cmpi', 'sort_order' => 2]);
        SocialLink::create(['platform' => 'email', 'url' => 'mailto:info@cmpi.edu.bd', 'sort_order' => 3]);
        SocialLink::create(['platform' => 'phone', 'url' => 'tel:+880341000000', 'sort_order' => 4]);

        // Admissions (dummy applications)
        Admission::create([
            'application_id' => 'ADM-2026-001',
            'name' => 'Tanvir Ahmed',
            'email' => 'tanvir@example.com',
            'phone' => '+880 1712-345678',
            'department' => 'Computer Science & Technology',
            'session' => '2026-2027',
            'ssc_gpa' => '4.80',
            'hsc_gpa' => '4.20',
            'father_name' => 'Md. Rafiq Ahmed',
            'mother_name' => 'Rashida Begum',
            'address' => 'Ukhia, Cox\'s Bazar',
            'blood_group' => 'B+',
            'status' => 'pending',
        ]);
        Admission::create([
            'application_id' => 'ADM-2026-002',
            'name' => 'Sumaiya Khatun',
            'email' => 'sumaiya@example.com',
            'phone' => '+880 1812-987654',
            'department' => 'Civil Technology',
            'session' => '2026-2027',
            'ssc_gpa' => '4.50',
            'hsc_gpa' => '3.90',
            'father_name' => 'Abdul Karim',
            'mother_name' => 'Rahima Khatun',
            'address' => 'Maheshkhali, Cox\'s Bazar',
            'blood_group' => 'A+',
            'status' => 'approved',
        ]);
        Admission::create([
            'application_id' => 'ADM-2026-003',
            'name' => 'Rakibul Hasan',
            'email' => 'rakib@example.com',
            'phone' => '+880 1912-555123',
            'department' => 'Electrical Technology',
            'session' => '2026-2027',
            'ssc_gpa' => '4.20',
            'hsc_gpa' => '3.80',
            'father_name' => 'Abdur Rashid',
            'mother_name' => 'Salma Begum',
            'address' => 'Chakaria, Cox\'s Bazar',
            'blood_group' => 'O+',
            'status' => 'pending',
        ]);

        // Class Routines
        ClassRoutine::create([
            'department' => 'Computer Science & Technology',
            'semester' => '4th',
            'academic_year' => '2026',
            'title' => 'CST 4th Semester Routine - Summer 2026',
            'pdf_path' => '/routines/cst-4th-summer-2026.pdf',
            'original_name' => 'CST_4th_Semester_Routine_2026.pdf',
        ]);
        ClassRoutine::create([
            'department' => 'Civil Technology',
            'semester' => '4th',
            'academic_year' => '2026',
            'title' => 'Civil 4th Semester Routine - Summer 2026',
            'pdf_path' => '/routines/civil-4th-summer-2026.pdf',
            'original_name' => 'Civil_4th_Semester_Routine_2026.pdf',
        ]);
        ClassRoutine::create([
            'department' => 'Electrical Technology',
            'semester' => '2nd',
            'academic_year' => '2026',
            'title' => 'EEE 2nd Semester Routine - Summer 2026',
            'pdf_path' => '/routines/eee-2nd-summer-2026.pdf',
            'original_name' => 'EEE_2nd_Semester_Routine_2026.pdf',
        ]);
    }
}