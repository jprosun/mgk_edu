<?php
/**
 * Seed batch 2: 10 more teachers (IDs 16-25) with full Phase 1+2 data.
 * Run: wp eval-file seed/seed-teachers-batch2.php --allow-root --path=/var/www/html
 */

// ── Ensure taxonomy terms exist ──────────────────────────────────────────────
$extra_subjects = [ 'GP', 'History', 'Geography', 'Biology', 'Literature', 'Tamil', 'Malay', 'Economics' ];
foreach ( $extra_subjects as $s ) {
    if ( ! term_exists( $s, 'mgk_subject' ) ) wp_insert_term( $s, 'mgk_subject' );
}
$extra_levels = [ 'Preschool' ];
foreach ( $extra_levels as $l ) {
    if ( ! term_exists( $l, 'mgk_level' ) ) wp_insert_term( $l, 'mgk_level' );
}
echo "Terms ensured.\n";

if ( ! function_exists( 'mgk_seed_tutor_email' ) ) {
    function mgk_seed_tutor_email( $name ) {
        $slug = sanitize_title( (string) $name );
        $slug = str_replace( '-', '.', $slug );
        return sanitize_email( $slug . '@tutors.margick.test' );
    }
}

// ── Teacher definitions ──────────────────────────────────────────────────────
$teachers = [
    [
        'name'       => 'Mr. Arjun Nair',
        'bio'        => 'JC Biology and Chemistry tutor who uses mind maps and past-paper analysis to deliver consistent A results.',
        'tier'       => 'Full-time', 'experience' => '6y', 'rate_num' => 65, 'trial' => '$40',
        'rating'     => '4.8', 'reviews' => '49', 'response' => '3h',
        'locations'  => [ 'Central SG', 'Online' ],
        'tags'       => 'H2 Bio, H2 Chem, A-Level, Mind maps',
        'subjects'   => [ 'Biology', 'H2 Chemistry', 'Chemistry' ],
        'levels'     => [ 'JC' ],
        'short_name' => 'Mr Arjun', 'credential_badge' => 'NUS-grad',
        'languages'  => 'English, Tamil', 'duration' => '2h · 1-2x/week',
        'active_students' => 11, 'last_active' => '1d',
        'philosophy' => 'Biology rewards students who can explain processes in sequence. I train that first, then layer in exam keywords.',
        'about_paragraphs' => [
            'NUS Life Sciences graduate tutoring H2 Biology and H2 Chemistry for JC students. Strong track record with students who struggle with content-heavy topics.',
            'Mr Arjun is known for his topic mind maps, which compress an entire chapter into one clear diagram students can recall under exam pressure.',
        ],
        'specializations' => [ ['H2 Biology', 'Expert'], ['H2 Chemistry', 'Strong'], ['Mind map system', 'Specialist'], ['JC essay answers', 'Expert'] ],
        'qualifications' => [
            ['B.Sc. Life Sciences (Hons)', 'NUS · 2014', 'Second Upper'],
            ['Full-time Tutor', 'Since 2018', 'Margick verified'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['49', 'Reviews'], ['6y', 'Tutoring exp'], ['85%', 'A rate A-Level'] ],
        'packages'    => [ ['Trial lesson', '$40', 'First lesson 38% off', true], ['8 lessons', '$500', '4% saving', false], ['16 lessons', '$936', '10% saving', false] ],
        'faqs'        => [
            ['q' => 'Does Mr Arjun cover H1 Biology?', 'a' => 'He focuses on H2. For H1, some overlap is possible — enquire directly.'],
            ['q' => 'Does he provide notes?', 'a' => 'Yes. Chapter mind maps and keyword tables after each topic.'],
        ],
    ],
    [
        'name'       => 'Ms. Tan Xiao Ling',
        'bio'        => 'Dedicated Malay and English tutor for Secondary students, with strong results at O-Level in both subjects.',
        'tier'       => 'Full-time', 'experience' => '7y', 'rate_num' => 55, 'trial' => '$35',
        'rating'     => '4.7', 'reviews' => '61', 'response' => '4h',
        'locations'  => [ 'East', 'NE', 'Online' ],
        'tags'       => 'O-Level Malay, English, Sec 3-4',
        'subjects'   => [ 'Malay', 'English' ],
        'levels'     => [ 'Sec 1-2', 'Sec 3-4' ],
        'short_name' => 'Ms Tan', 'credential_badge' => 'Full-time tutor',
        'languages'  => 'English, Malay, Mandarin', 'duration' => '1.5h · 1-2x/week',
        'active_students' => 13, 'last_active' => '2d',
        'philosophy' => 'Language exams are predictable once you know the patterns. I teach students to recognise question types and respond with ready-made structures.',
        'about_paragraphs' => [
            'Full-time tutor with 7 years of O-Level Malay and English experience. Works mainly with Sec 3–4 students preparing for national exams.',
            'Ms Tan is methodical — she uses an exam-blueprint approach where students map each question type to a fixed answering framework before the exam.',
        ],
        'specializations' => [ ['O-Level Malay', 'Expert'], ['O-Level English', 'Strong'], ['Exam blueprints', 'Specialist'], ['Oral prep', 'Strong'] ],
        'qualifications' => [
            ['B.A. Malay Studies', 'NUS · 2013', 'Second Upper'],
            ['Full-time Tutor', 'Since 2017', 'Margick verified'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['61', 'Reviews'], ['7y', 'Tutoring exp'], ['88%', 'B3 or better O-Level'], ['86%', 'Renewal rate'] ],
        'packages'    => [ ['Trial lesson', '$35', 'First lesson 36% off', true], ['8 lessons', '$424', '4% saving', false], ['16 lessons', '$800', '9% saving', false] ],
        'faqs'        => [
            ['q' => 'Can Ms Tan teach both Malay and English?', 'a' => 'Yes. Many students book both — she schedules them on the same day.'],
            ['q' => 'Does she cover MTL B?', 'a' => 'Yes. She is experienced with both standard and MTL B Malay.'],
        ],
    ],
    [
        'name'       => 'Mr. Kevin Yap',
        'bio'        => 'JC GP and History specialist who trains students to construct well-evidenced arguments quickly under time pressure.',
        'tier'       => 'Ex-MOE', 'experience' => '10y', 'rate_num' => 80, 'trial' => '$55',
        'rating'     => '4.8', 'reviews' => '57', 'response' => '5h',
        'locations'  => [ 'Central SG', 'West', 'Online' ],
        'tags'       => 'GP, A-Level History, Essay, Argument structure',
        'subjects'   => [ 'GP', 'History' ],
        'levels'     => [ 'JC' ],
        'short_name' => 'Mr Kevin', 'credential_badge' => 'Ex-MOE',
        'languages'  => 'English', 'duration' => '2h · 1-2x/week',
        'active_students' => 9, 'last_active' => '2d',
        'philosophy' => 'A strong argument is not an opinion — it is a claim, evidence, and link to the question. I drill that sequence until it is automatic.',
        'about_paragraphs' => [
            'Former MOE JC teacher with 10 years specialising in General Paper and H2 History. Now tutoring privately with a focus on essay structure and content depth.',
            'Mr Kevin is especially effective with students who write fluent English but lose marks for weak argument structure or insufficient specific evidence.',
        ],
        'specializations' => [ ['GP essay structure', 'Expert'], ['H2 History essays', 'Specialist'], ['AQ answering', 'Expert'], ['Current affairs', 'Strong'] ],
        'qualifications' => [
            ['B.A. History (Hons)', 'NUS · 2008', 'First Class'],
            ['PGDE (JC)', 'NIE · 2010', 'Cert ID: NIE-PGDE-2010-2244'],
            ['MOE JC Teacher', '2010–2020', 'GP and History verified'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['57', 'Reviews'], ['10y', 'Teaching exp'], ['89%', 'Distinction rate GP'], ['91%', 'Renewal rate'] ],
        'packages'    => [ ['Trial lesson', '$55', 'First lesson 31% off', true], ['8 lessons', '$616', '4% saving', false], ['16 lessons', '$1,152', '10% saving', false] ],
        'faqs'        => [
            ['q' => 'Does Mr Kevin cover H1 GP only or H2 History too?', 'a' => 'Both. He teaches GP (all JC1-2) and H2 History (Sec-JC transition welcome).'],
            ['q' => 'Does he provide model essays?', 'a' => 'Yes. Annotated model essays and question banks from top JCs after each topic.'],
        ],
    ],
    [
        'name'       => 'Ms. Nur Aisha Binte Rashid',
        'bio'        => 'Nurturing Primary English and Tamil tutor with a child-first approach and strong PSLE preparation record.',
        'tier'       => 'Part-time', 'experience' => '5y', 'rate_num' => 45, 'trial' => '$28',
        'rating'     => '4.7', 'reviews' => '38', 'response' => '6h',
        'locations'  => [ 'NE', 'East', 'Online' ],
        'tags'       => 'Tamil, PSLE English, P4-P6, Child-friendly',
        'subjects'   => [ 'Tamil', 'English' ],
        'levels'     => [ 'P1-P4', 'P5-P6' ],
        'short_name' => 'Ms Aisha', 'credential_badge' => 'Part-time tutor',
        'languages'  => 'English, Tamil, Malay', 'duration' => '1h or 1.5h · 1-2x/week',
        'active_students' => 10, 'last_active' => '3d',
        'philosophy' => 'Children who enjoy lessons learn faster. I make language fun first, then structured — not the other way around.',
        'about_paragraphs' => [
            'Part-time tutor for Primary English and Tamil. Warm, encouraging, and experienced with students who are nervous about language exams.',
            'Ms Aisha focuses on building vocabulary and sentence structure through games, stories, and guided writing before moving to exam-style practice.',
        ],
        'specializations' => [ ['Tamil PSLE', 'Expert'], ['P4-P6 English', 'Strong'], ['Young learners', 'Specialist'], ['Vocabulary building', 'Expert'] ],
        'qualifications' => [
            ['B.A. Tamil Studies', 'NUS · 2016', 'Second Upper'],
            ['Part-time Tutor', 'Since 2019', 'Margick verified'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['38', 'Reviews'], ['5y', 'Tutoring exp'], ['4.7', 'Avg rating'], ['80%', 'Renewal rate'] ],
        'packages'    => [ ['Trial lesson', '$28', 'First lesson 38% off', true], ['8 lessons', '$347', '4% saving', false], ['12 lessons', '$513', '5% saving', false] ],
        'faqs'        => [
            ['q' => 'Does Ms Aisha teach Tamil at all levels?', 'a' => 'P1–P6 Tamil. For O-Level Tamil, please enquire directly.'],
            ['q' => 'How does she approach shy students?', 'a' => 'She starts with low-pressure activities and builds confidence gradually before any exam practice.'],
        ],
    ],
    [
        'name'       => 'Mr. James Liu',
        'bio'        => 'IB and JC English Literature specialist who teaches close reading, comparative essays, and independent analysis.',
        'tier'       => 'IB Specialist', 'experience' => '9y', 'rate_num' => 90, 'trial' => '$60',
        'rating'     => '4.9', 'reviews' => '44', 'response' => '3h',
        'locations'  => [ 'Central SG', 'Online' ],
        'tags'       => 'IB English, GP, Literature, Essay',
        'subjects'   => [ 'Literature', 'GP', 'English' ],
        'levels'     => [ 'JC', 'IB' ],
        'short_name' => 'Mr James', 'credential_badge' => 'NUS-grad',
        'languages'  => 'English', 'duration' => '2h · 1-2x/week',
        'active_students' => 8, 'last_active' => '1d',
        'philosophy' => 'Literary analysis is about asking better questions of a text, not memorising pre-written answers. I teach students to question, then argue.',
        'about_paragraphs' => [
            'English Literature and GP specialist for JC and IB students. Works with students from ACJC, SRJC, Raffles, and international schools.',
            'Mr James trained as an academic and brings a university-level approach to JC/IB essays — rigorous argument structure, specific textual evidence, and original interpretation.',
        ],
        'specializations' => [ ['IB English Lang & Lit', 'Expert'], ['A-Level Literature', 'Expert'], ['GP Paper 1 essays', 'Strong'], ['Close reading', 'Specialist'] ],
        'qualifications' => [
            ['M.A. English Literature', 'NUS · 2013', 'Distinction'],
            ['B.A. English (Hons)', 'NUS · 2011', 'First Class'],
            ['Tutor registration', 'Margick verified 2022', 'ID: MGK-TUT-2022-0055'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['44', 'Reviews'], ['9y', 'Teaching exp'], ['91%', 'A rate'], ['94%', 'Renewal rate'] ],
        'packages'    => [ ['Trial lesson', '$60', 'First lesson 33% off', true], ['8 lessons', '$693', '4% saving', false], ['16 lessons', '$1,296', '10% saving', false] ],
        'faqs'        => [
            ['q' => 'Does Mr James cover both A-Level and IB English?', 'a' => 'Yes. He is equally experienced in both, including the IB Individual Oral Commentary.'],
            ['q' => 'Does he help with the IB Extended Essay?', 'a' => 'Yes, for English category EEs. He can guide structure, argument, and bibliography.'],
        ],
    ],
    [
        'name'       => 'Ms. Wong Shu Fen',
        'bio'        => 'Lower secondary Science and Math tutor who builds strong foundations before students enter the high-stakes Sec 3 year.',
        'tier'       => 'Full-time', 'experience' => '5y', 'rate_num' => 50, 'trial' => '$32',
        'rating'     => '4.7', 'reviews' => '42', 'response' => '5h',
        'locations'  => [ 'West', 'Central SG', 'Online' ],
        'tags'       => 'Sec 1-2 Science, Math, Foundation, O-Level prep',
        'subjects'   => [ 'Science', 'Math', 'E-Math' ],
        'levels'     => [ 'Sec 1-2' ],
        'short_name' => 'Ms Wong', 'credential_badge' => 'Full-time tutor',
        'languages'  => 'English, Mandarin', 'duration' => '1.5h · 1-2x/week',
        'active_students' => 12, 'last_active' => '2d',
        'philosophy' => 'The Sec 3 jump is predictable. If I can close the gaps in Sec 1–2, students arrive at Sec 3 already confident instead of overwhelmed.',
        'about_paragraphs' => [
            'Full-time tutor specialising in Sec 1–2 Science and Math. Her focus is on closing foundational gaps before students face the higher-stakes Sec 3 curriculum.',
            'Ms Wong is known for her clear teaching notes and progress tracking spreadsheet, which she shares with parents monthly.',
        ],
        'specializations' => [ ['Sec 1-2 Science', 'Expert'], ['Sec 1-2 Math', 'Strong'], ['Foundation repair', 'Specialist'], ['Sec 3 prep', 'Strong'] ],
        'qualifications' => [
            ['B.Sc. Biology', 'NTU · 2015', 'Second Upper'],
            ['Full-time Tutor', 'Since 2019', 'Margick verified'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['42', 'Reviews'], ['5y', 'Tutoring exp'], ['4.7', 'Avg rating'], ['83%', 'Renewal rate'] ],
        'packages'    => [ ['Trial lesson', '$32', 'First lesson 36% off', true], ['8 lessons', '$386', '4% saving', false], ['16 lessons', '$720', '10% saving', false] ],
        'faqs'        => [
            ['q' => 'Does Ms Wong take Sec 3 students?', 'a' => 'Occasionally, if slots are open. Her focus is Sec 1–2 foundation building.'],
            ['q' => 'Does she cover Pure Science?', 'a' => 'She covers combined Science at Sec 1–2 and can prepare students for either Pure or Combined Science at Sec 3.'],
        ],
    ],
    [
        'name'       => 'Mr. Prakash Pillai',
        'bio'        => 'H2 Biology and H2 Chemistry JC tutor with a systematic topic-by-topic breakdown and strong IA/SPA guidance.',
        'tier'       => 'Premium', 'experience' => '11y', 'rate_num' => 105, 'trial' => '$70',
        'rating'     => '4.9', 'reviews' => '36', 'response' => '2h',
        'locations'  => [ 'Central SG', 'Online' ],
        'tags'       => 'H2 Bio, H2 Chem, SPA, A-Level, Premium',
        'subjects'   => [ 'Biology', 'H2 Chemistry', 'Chemistry' ],
        'levels'     => [ 'JC', 'IB' ],
        'short_name' => 'Mr Prakash', 'credential_badge' => 'NUS PhD',
        'languages'  => 'English, Tamil', 'duration' => '2h · 2x/week',
        'active_students' => 6, 'last_active' => '1d',
        'philosophy' => 'Biology is chemistry is physics — it all connects. Once students see those links, hard topics stop feeling hard.',
        'about_paragraphs' => [
            'PhD-trained JC Sciences tutor with 11 years delivering A and B results in H2 Biology and H2 Chemistry. Also covers IB HL Biology and Chemistry.',
            'Mr Prakash is one of the few tutors who provides structured SPA (School-based Practical Assessment) coaching, a component most tutors skip.',
        ],
        'specializations' => [ ['H2 Biology', 'Expert'], ['H2 Chemistry', 'Expert'], ['SPA coaching', 'Specialist'], ['IB HL Sciences', 'Strong'] ],
        'qualifications' => [
            ['PhD Biochemistry', 'NUS · 2010', 'Thesis: Cell Signalling'],
            ['B.Sc. Biochemistry (Hons)', 'NUS · 2005', 'First Class'],
            ['MOE JC Science (retired)', '2010–2019', 'H2 Biology / Chemistry'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['36', 'Reviews'], ['11y', 'Exp'], ['92%', 'A rate'], ['97%', 'Renewal rate'] ],
        'packages'    => [ ['Trial lesson', '$70', 'First lesson 33% off', true], ['8 lessons', '$808', '4% saving', false], ['16 lessons', '$1,512', '10% saving', false] ],
        'faqs'        => [
            ['q' => 'Does Mr Prakash cover SPA?', 'a' => 'Yes. SPA coaching is included in his package sessions at JC level.'],
            ['q' => 'Can he help with IB Internal Assessment?', 'a' => 'Yes. He guides HL Biology and Chemistry IAs from research question to data analysis.'],
        ],
    ],
    [
        'name'       => 'Ms. Amanda Koh',
        'bio'        => 'Secondary English and Literature tutor known for fast essay improvement and clear marking feedback.',
        'tier'       => 'Full-time', 'experience' => '6y', 'rate_num' => 58, 'trial' => '$38',
        'rating'     => '4.8', 'reviews' => '53', 'response' => '4h',
        'locations'  => [ 'East', 'Central SG', 'Online', 'Home tuition' ],
        'tags'       => 'O-Level English, Literature, Sec 3-4, Essays',
        'subjects'   => [ 'English', 'Literature' ],
        'levels'     => [ 'Sec 1-2', 'Sec 3-4' ],
        'short_name' => 'Ms Amanda', 'credential_badge' => 'Full-time tutor',
        'languages'  => 'English', 'duration' => '1.5h · 1-2x/week',
        'active_students' => 14, 'last_active' => '2d',
        'philosophy' => 'Students improve essays fastest when they understand exactly why marks were deducted. I mark in real time during lessons, not after.',
        'about_paragraphs' => [
            'Full-time English and Literature tutor for Sec 1–4 students. Specialises in essay writing and O-Level Literature (prose and poetry).',
            'Ms Amanda marks and discusses student work during lessons, not between sessions — this live feedback loop is what drives fast improvement in her students.',
        ],
        'specializations' => [ ['O-Level Essay', 'Expert'], ['O-Level Literature', 'Strong'], ['Live marking feedback', 'Specialist'], ['Comprehension', 'Expert'] ],
        'qualifications' => [
            ['B.A. English Literature', 'NTU · 2014', 'Second Upper'],
            ['Full-time Tutor', 'Since 2018', 'Margick verified'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['53', 'Reviews'], ['6y', 'Tutoring exp'], ['4.8', 'Avg rating'], ['87%', 'Renewal rate'] ],
        'packages'    => [ ['Trial lesson', '$38', 'First lesson 34% off', true], ['8 lessons', '$447', '4% saving', false], ['16 lessons', '$835', '10% saving', false] ],
        'faqs'        => [
            ['q' => 'Does Ms Amanda teach both English and Literature?', 'a' => 'Yes. Most of her Sec 3-4 students take both and she schedules them together.'],
            ['q' => 'Does she provide practice essays to mark?', 'a' => 'Yes. She provides one timed essay prompt per week as optional homework.'],
        ],
    ],
    [
        'name'       => 'Mr. Zach Ong',
        'bio'        => 'High-energy Sec Math tutor with a reputation for turning D-grade students into confident B and A scorers within one term.',
        'tier'       => 'Full-time', 'experience' => '4y', 'rate_num' => 52, 'trial' => '$33',
        'rating'     => '4.8', 'reviews' => '47', 'response' => '3h',
        'locations'  => [ 'NE', 'East', 'Online' ],
        'tags'       => 'A-Math, E-Math, Sec 3-4, D-to-B specialist',
        'subjects'   => [ 'A-Math', 'E-Math' ],
        'levels'     => [ 'Sec 3-4' ],
        'short_name' => 'Mr Zach', 'credential_badge' => 'Full-time tutor',
        'languages'  => 'English, Mandarin', 'duration' => '2h · 2x/week',
        'active_students' => 16, 'last_active' => '1d',
        'philosophy' => 'Failing Math is almost always about missing one or two foundational concepts. Find those, fix those, and the rest follows quickly.',
        'about_paragraphs' => [
            'Full-time Sec Math tutor known for rapid results with struggling students. His "gap audit" at the start of every new engagement identifies exactly where to begin.',
            'Mr Zach is energetic and motivating — students who dread Math lessons often say his sessions are the first time they have enjoyed the subject.',
        ],
        'specializations' => [ ['Sec 3-4 A-Math', 'Expert'], ['E-Math recovery', 'Specialist'], ['Gap audit', 'Unique method'], ['Struggling students', 'Expert'] ],
        'qualifications' => [
            ['B.Sc. Mathematics', 'SIM-UOL · 2017', 'Second Upper'],
            ['Full-time Tutor', 'Since 2020', 'Margick verified'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['47', 'Reviews'], ['4y', 'Tutoring exp'], ['4.8', 'Avg rating'], ['85%', 'D-to-B or better'] ],
        'packages'    => [ ['Trial lesson', '$33', 'First lesson 37% off', true], ['8 lessons', '$401', '4% saving', false], ['16 lessons', '$749', '10% saving', false] ],
        'faqs'        => [
            ['q' => 'Can Mr Zach help a student who is failing Math?', 'a' => 'Yes. His gap audit process is specifically designed for this situation.'],
            ['q' => 'How quickly do students improve?', 'a' => 'Most students see a grade improvement within 6–8 weeks. He sets mini-targets every 2 weeks to track progress.'],
        ],
    ],
    [
        'name'       => 'Ms. Tan Hui Lin',
        'bio'        => 'Experienced Secondary and JC Chinese tutor with a strong oral coaching method and proven composition framework.',
        'tier'       => 'Ex-MOE', 'experience' => '12y', 'rate_num' => 75, 'trial' => '$50',
        'rating'     => '4.9', 'reviews' => '88', 'response' => '4h',
        'locations'  => [ 'North', 'Central SG', 'Online', 'Home tuition' ],
        'tags'       => 'O-Level Chinese, Higher Chinese, Oral, Composition',
        'subjects'   => [ 'Chinese', 'Higher Chinese' ],
        'levels'     => [ 'Sec 1-2', 'Sec 3-4', 'JC' ],
        'short_name' => 'Ms Hui Lin', 'credential_badge' => 'NIE-trained',
        'languages'  => 'English, Mandarin, Cantonese', 'duration' => '1.5h or 2h · 1-2x/week',
        'active_students' => 18, 'last_active' => '1d',
        'philosophy' => 'Chinese composition is about ideas first. Once students have something real to say, the language follows. I teach thinking before writing.',
        'about_paragraphs' => [
            'Ex-MOE Chinese teacher with 12 years in Secondary and JC level. Specialises in O-Level and A-Level Chinese with a structured composition framework that is easy to apply under exam conditions.',
            'Ms Tan Hui Lin is especially effective with students from English-dominant homes who need to bridge the vocabulary and expression gap quickly before national exams.',
        ],
        'specializations' => [ ['O-Level Chinese', 'Expert'], ['Higher Chinese', 'Expert'], ['Oral coaching', 'Specialist'], ['A-Level Chinese', 'Strong'] ],
        'qualifications' => [
            ['B.A. Chinese Studies (Hons)', 'NTU · 2005', 'First Class'],
            ['NIE PGDE (Secondary)', 'NIE · 2007', 'Cert ID: NIE-PGDE-2007-3310'],
            ['MOE Teacher', '2007–2019', 'Secondary and JC Chinese'],
            ['Background Check', 'Annual update', 'Last check: Jan 2026'],
        ],
        'track_stats' => [ ['88', 'Reviews'], ['12y', 'Teaching exp'], ['4.9', 'Avg rating'], ['92%', 'Renewal rate'], ['90%', 'B3 or better rate'] ],
        'packages'    => [ ['Trial lesson', '$50', 'First lesson 33% off', true], ['8 lessons', '$578', '4% saving', false], ['16 lessons', '$1,080', '10% saving', false] ],
        'faqs'        => [
            ['q' => 'Does Ms Tan teach A-Level Chinese?', 'a' => 'Yes. She covers H1 Chinese and H2 Chinese Language & Literature.'],
            ['q' => 'Can she help English-dominant students?', 'a' => 'Yes. She has a specific 6-week vocabulary sprint programme for students starting from a low base.'],
        ],
    ],
];

// ── Insert teachers + profiles ────────────────────────────────────────────────
$created = 0;
foreach ( $teachers as $t ) {
    $existing = get_posts( [ 'post_type' => 'mg_teacher', 'post_status' => 'publish', 'title' => $t['name'], 'numberposts' => 1 ] );
    if ( $existing ) {
        $existing_id = (int) $existing[0]->ID;
        if ( ! get_post_meta( $existing_id, 'mgk_tutor_email', true ) ) {
            update_post_meta( $existing_id, 'mgk_tutor_email', mgk_seed_tutor_email( $t['name'] ) );
        }
        echo "Skip (exists): {$t['name']}\n";
        continue;
    }

    $post_id = wp_insert_post( [
        'post_type'    => 'mg_teacher',
        'post_status'  => 'publish',
        'post_title'   => $t['name'],
        'post_name'    => sanitize_title( $t['name'] ),
        'post_excerpt' => $t['bio'],
        'post_content' => $t['bio'],
    ] );
    if ( is_wp_error( $post_id ) ) { echo "ERROR: {$t['name']}\n"; continue; }

    // Listing meta
    foreach ( [ 'tier', 'experience', 'rate_num', 'trial', 'rating', 'reviews', 'response', 'tags', 'bio' ] as $k ) {
        $meta_k = $k === 'trial' ? 'mgk_trial_price' : "mgk_$k";
        if ( isset( $t[ $k ] ) ) update_post_meta( $post_id, $meta_k, $t[ $k ] );
    }
    update_post_meta( $post_id, 'mgk_locations', $t['locations'] );
    update_post_meta( $post_id, 'mgk_tutor_email', mgk_seed_tutor_email( $t['name'] ) );

    // Profile Phase 1 meta
    foreach ( [ 'short_name', 'credential_badge', 'languages', 'duration', 'active_students', 'last_active', 'philosophy' ] as $k ) {
        if ( isset( $t[ $k ] ) ) update_post_meta( $post_id, "mgk_$k", $t[ $k ] );
    }
    update_post_meta( $post_id, 'mgk_demo_video_url', '' );

    // Profile Phase 2 repeaters via ACF
    if ( function_exists( 'update_field' ) ) {
        update_field( 'mgk_about_paragraphs', array_map( fn($p) => [ 'content' => $p ], $t['about_paragraphs'] ), $post_id );
        update_field( 'mgk_specializations',  array_map( fn($r) => [ 'specialty' => $r[0], 'level' => $r[1] ], $t['specializations'] ), $post_id );
        update_field( 'mgk_qualifications',   array_map( fn($r) => [ 'title' => $r[0], 'description' => $r[1], 'cert_id' => $r[2] ], $t['qualifications'] ), $post_id );
        update_field( 'mgk_track_stats',      array_map( fn($r) => [ 'value' => $r[0], 'label' => $r[1] ], $t['track_stats'] ), $post_id );
        update_field( 'mgk_packages',         array_map( fn($r) => [ 'name' => $r[0], 'price' => $r[1], 'description' => $r[2], 'featured' => $r[3] ], $t['packages'] ), $post_id );
        update_field( 'mgk_faqs',             $t['faqs'], $post_id );
    }

    // Taxonomies
    $subj_ids = array_filter( array_map( fn($s) => ( $term = get_term_by( 'name', $s, 'mgk_subject' ) ) ? $term->term_id : null, $t['subjects'] ) );
    $lvl_ids  = array_filter( array_map( fn($l) => ( $term = get_term_by( 'name', $l, 'mgk_level' ) ) ? $term->term_id : null, $t['levels'] ) );
    if ( $subj_ids ) wp_set_post_terms( $post_id, array_values( $subj_ids ), 'mgk_subject' );
    if ( $lvl_ids )  wp_set_post_terms( $post_id, array_values( $lvl_ids ),  'mgk_level' );

    echo "Created: {$t['name']} (ID $post_id)\n";
    $created++;
}

echo "\nDone. Created $created teachers.\n";
echo "Total mg_teacher: " . wp_count_posts('mg_teacher')->publish . "\n";
