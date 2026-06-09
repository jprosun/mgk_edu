<?php
/**
 * Seed Phase 1 + Phase 2 profile fields for all mg_teacher posts.
 * Run: wp eval-file seed/seed-teacher-profiles.php --allow-root --path=/var/www/html
 */

$profiles = [

    'ms-lee-yi-ling' => [
        'short_name'       => 'Ms Lee',
        'credential_badge' => 'NIE-trained',
        'languages'        => 'English, Mandarin',
        'duration'         => '1.5h or 2h · 1-2x/week',
        'active_students'  => 12,
        'last_active'      => '2d',
        'demo_video_url'   => '',
        'philosophy'       => 'I teach students to explain each step clearly before rushing to the answer. Once the thinking is stable, speed and confidence improve naturally.',
        'about_paragraphs' => [
            'Former MOE teacher specialising in upper-primary Math and PSLE Science. Lessons are structured around diagnosis, worked examples, guided practice, and a short parent update after each session.',
            'Ms Lee is especially strong with students who understand concepts in class but lose marks in word problems, careless working, or time pressure.',
        ],
        'specializations'  => [
            [ 'PSLE Math prep',      '87% A/A* rate' ],
            [ 'Word problems',       'Expert' ],
            [ 'Visual learners',     'Strong' ],
            [ 'Confidence building', 'Expert' ],
        ],
        'qualifications'   => [
            [ 'B.Sc. Mathematics',       'NUS · 2008',                                'Cert ID: NUS-MATH-2008-1234' ],
            [ 'NIE PGDE (Primary)',       'National Institute of Education · 2010',    'Cert ID: NIE-PGDE-2010-5678' ],
            [ 'MOE Registered Teacher',  '2010–2018 · Primary School Math/Sci',       'Service record verified' ],
            [ 'Background Check',        'Criminal record · Annual update',           'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '200+', 'Students taught' ],
            [ '87%',  'PSLE A/A* rate' ],
            [ '2.5',  'Avg grade up' ],
            [ '95%',  'Renewal rate' ],
            [ '8y',   'Teaching exp' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$40',  'First lesson 40% off',  true  ],
            [ '8 lessons',    '$494', '5% package saving',     false ],
            [ '16 lessons',   '$936', '10% package saving',    false ],
        ],
        'faqs'             => [
            [ 'q' => 'How does Ms Lee structure a lesson?',  'a' => 'Each lesson starts with error review, then concept teaching, guided practice, and a short parent update.' ],
            [ 'q' => 'Does she provide materials?',          'a' => 'Yes. Custom worksheets and exam-style questions based on the student diagnosis.' ],
            [ 'q' => 'Can lessons be online?',               'a' => 'Yes. Online lessons use a shared whiteboard and post-lesson notes.' ],
            [ 'q' => 'What is the cancellation policy?',     'a' => 'Reschedule requests should be made at least 24 hours before the lesson.' ],
        ],
    ],

    'mr-tan-jun-wei' => [
        'short_name'       => 'Mr Tan',
        'credential_badge' => 'Full-time tutor',
        'languages'        => 'English, Mandarin',
        'duration'         => '2h · 2x/week',
        'active_students'  => 18,
        'last_active'      => '1d',
        'demo_video_url'   => '',
        'philosophy'       => 'I drill patterns until they are automatic, then switch to exam conditions so students are never surprised on test day.',
        'about_paragraphs' => [
            'Full-time Math specialist for upper secondary students. Known for breaking down A-Math and E-Math into short, repeatable exam techniques.',
            'Mr Tan consistently reduces careless mistakes within 4 weeks by targeting each student\'s specific error patterns with targeted drill sets.',
        ],
        'specializations'  => [
            [ 'A-Math O-Level',    '91% B3 or better' ],
            [ 'E-Math',            'Expert' ],
            [ 'Careless mistakes', 'Targeted drills' ],
            [ 'Exam confidence',   'Strong' ],
        ],
        'qualifications'   => [
            [ 'B.Sc. Mathematics (Hons)', 'NTU · 2015',           'Cert ID: NTU-MATH-2015-0092' ],
            [ 'Full-time Tutor',          'Since 2016',            'Registered with Margick' ],
            [ 'Background Check',         'Annual update',         'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '124', 'Reviews' ],
            [ '91%', 'B3 or better O-Level' ],
            [ '5y',  'Full-time exp' ],
            [ '89%', 'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$35',  'First lesson 35% off',  true  ],
            [ '8 lessons',    '$424', '4% package saving',     false ],
            [ '16 lessons',   '$800', '9% package saving',     false ],
        ],
        'faqs'             => [
            [ 'q' => 'What level does Mr Tan teach?',    'a' => 'Mainly Sec 3–4 A-Math and E-Math (O-Level track).' ],
            [ 'q' => 'Does he provide past papers?',     'a' => 'Yes. He uses 10-year series and school prelim papers from top schools.' ],
            [ 'q' => 'How are lessons structured?',      'a' => 'Error review → concept → timed practice → debrief. Always ends with a short self-assessment.' ],
        ],
    ],

    'ms-goh-ai-wei' => [
        'short_name'       => 'Ms Goh',
        'credential_badge' => 'NUS-grad',
        'languages'        => 'English, Mandarin',
        'duration'         => '1.5h · 1-2x/week',
        'active_students'  => 9,
        'last_active'      => '3d',
        'demo_video_url'   => '',
        'philosophy'       => 'Every child learns at their own pace. My job is to find the gap, explain it clearly, and give enough practice until it feels easy.',
        'about_paragraphs' => [
            'NUS undergraduate tutoring Primary Math and English. Patient, encouraging, and great with anxious learners who need extra time to build understanding.',
            'Ms Goh focuses heavily on foundation skills before moving to harder questions. Her students often say they finally "get it" after a few sessions.',
        ],
        'specializations'  => [
            [ 'Primary Math P1-P6', 'Strong' ],
            [ 'English composition', 'Expert' ],
            [ 'Slow learners',      'Specialist' ],
            [ 'Foundation building', 'Expert' ],
        ],
        'qualifications'   => [
            [ 'B.Sc. Statistics (Hons)', 'NUS · Expected 2026',  'Undergraduate, year 3' ],
            [ 'Tutor registration',      'Margick verified 2023', 'ID: MGK-TUT-2023-0341' ],
            [ 'Background Check',        'Annual update',         'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '62',  'Reviews' ],
            [ '3y',  'Tutoring exp' ],
            [ '4.8', 'Avg rating' ],
            [ '82%', 'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$30',  'First lesson 33% off',  true  ],
            [ '8 lessons',    '$348', '3% package saving',     false ],
            [ '12 lessons',   '$510', '6% package saving',     false ],
        ],
        'faqs'             => [
            [ 'q' => 'What levels does Ms Goh cover?',  'a' => 'P1–P6 Math and English. She is best suited for students who need patience and foundational work.' ],
            [ 'q' => 'Does she give homework?',         'a' => 'Yes. Short, focused practice sets tailored to each student\'s current gaps.' ],
        ],
    ],

    'mr-wong-kai-ming' => [
        'short_name'       => 'Mr Wong',
        'credential_badge' => 'PhD (NUS)',
        'languages'        => 'English, Mandarin',
        'duration'         => '2h · 2x/week',
        'active_students'  => 8,
        'last_active'      => '1d',
        'demo_video_url'   => '',
        'philosophy'       => 'A-grade students do not just know the answer — they know exactly why every other answer is wrong. I train that precision.',
        'about_paragraphs' => [
            'PhD-trained tutor for high-achieving JC and IB students who need stretch beyond the standard syllabus. Specialises in H2 Math, H2 Chemistry, and cross-subject problem-solving.',
            'Mr Wong\'s students consistently score A in national exams, with an 87% A/A* rate across PSLE and A-Level subjects over the last 5 years.',
        ],
        'specializations'  => [
            [ 'H2 Math A-Level', '87% A rate' ],
            [ 'H2 Chemistry',    'Expert' ],
            [ 'IB HL',           'Strong' ],
            [ 'Stretch problems', 'Specialist' ],
        ],
        'qualifications'   => [
            [ 'PhD Chemistry',           'NUS · 2012',         'Thesis: Reaction Kinetics' ],
            [ 'B.Sc. Mathematics (Hons)', 'NUS · 2007',         'First Class' ],
            [ 'MOE Registered (JC)',      '2012–2018',          'JC Chemistry / Math' ],
            [ 'Background Check',         'Annual update',      'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '87%',  'A/A* rate' ],
            [ '12y',  'Teaching exp' ],
            [ '200+', 'JC/IB students' ],
            [ '97%',  'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$80',    'First lesson 33% off',  true  ],
            [ '8 lessons',    '$920',   '4% package saving',     false ],
            [ '16 lessons',   '$1,728', '10% package saving',    false ],
        ],
        'faqs'             => [
            [ 'q' => 'Does Mr Wong take O-Level students?',  'a' => 'He focuses on JC and IB. For O-Level, see Mr Tan or Mr Lim.' ],
            [ 'q' => 'What does a typical lesson look like?', 'a' => 'Hard questions first, then concept reinforcement, then exam simulation under timed conditions.' ],
            [ 'q' => 'Can he teach online?',                  'a' => 'Yes, with a digital tablet and shared workbook. Works well for JC theory.' ],
        ],
    ],

    'ms-sim-pei-hua' => [
        'short_name'       => 'Ms Sim',
        'credential_badge' => 'NIE-trained',
        'languages'        => 'English, Mandarin',
        'duration'         => '1.5h or 2h · 1-2x/week',
        'active_students'  => 14,
        'last_active'      => '2d',
        'demo_video_url'   => '',
        'philosophy'       => 'Parents who understand what is happening in lessons get better results. I keep them in the loop every session.',
        'about_paragraphs' => [
            'Ex-MOE bilingual specialist for Primary Math and Chinese. Structured parent communication every 2 weeks with detailed progress reports and suggested home practice.',
            'Ms Sim is especially effective for students preparing for PSLE who need strong Math and Chinese at the same time.',
        ],
        'specializations'  => [
            [ 'Bilingual PSLE',    'Math + Chinese' ],
            [ 'Parent reporting',  'Every 2 weeks' ],
            [ 'P5-P6 Math',        'Expert' ],
            [ 'Higher Chinese',    'Strong' ],
        ],
        'qualifications'   => [
            [ 'B.A. Chinese Studies',    'NUS · 2006',            'First Class' ],
            [ 'NIE PGDE (Primary)',       'NIE · 2008',            'Cert ID: NIE-PGDE-2008-3344' ],
            [ 'MOE Registered Teacher',  '2008–2018',             'Primary Math / Chinese' ],
            [ 'Background Check',        'Annual update',         'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '102', 'Reviews' ],
            [ '10y', 'Teaching exp' ],
            [ '4.9', 'Avg rating' ],
            [ '94%', 'Renewal rate' ],
            [ '85%', 'PSLE A/A* rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$50',  'First lesson 33% off',  true  ],
            [ '8 lessons',    '$578', '4% package saving',     false ],
            [ '16 lessons',   '$1,080', '10% package saving',  false ],
        ],
        'faqs'             => [
            [ 'q' => 'Can Ms Sim teach both Math and Chinese?',  'a' => 'Yes. Many of her students take both subjects with her. She can schedule back-to-back.' ],
            [ 'q' => 'What does her parent report include?',     'a' => 'Current level, gaps identified, what was covered, and suggested home practice tasks.' ],
        ],
    ],

    'mr-daniel-chen' => [
        'short_name'       => 'Mr Chen',
        'credential_badge' => 'PhD (NTU)',
        'languages'        => 'English, Mandarin',
        'duration'         => '2h · 1-2x/week',
        'active_students'  => 7,
        'last_active'      => '1d',
        'demo_video_url'   => '',
        'philosophy'       => 'Physics is not memorised — it is derived. Once you can derive the formula from scratch, you can handle any question variant.',
        'about_paragraphs' => [
            'NTU Physics PhD with 11 years tutoring JC and IB students. Uses visual derivations and concise exam-note systems that produce consistent A and B results.',
            'Mr Chen is known for turning abstract Physics into clear, logical chains. His students often say they understand concepts in one session that they struggled with for months.',
        ],
        'specializations'  => [
            [ 'H2 Physics A-Level', 'Expert' ],
            [ 'IB Physics HL/SL',   'Strong' ],
            [ 'Visual derivations', 'Specialist' ],
            [ 'H2 Math',            'Strong' ],
        ],
        'qualifications'   => [
            [ 'PhD Physics',           'NTU · 2011',         'Thesis: Quantum Transport' ],
            [ 'B.Sc. Physics (Hons)',  'NTU · 2006',         'First Class' ],
            [ 'Tutor registration',    'Margick verified',    'ID: MGK-TUT-2021-0177' ],
            [ 'Background Check',      'Annual update',       'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '54',  'Reviews' ],
            [ '11y', 'Teaching exp' ],
            [ '4.8', 'Avg rating' ],
            [ '88%', 'A/B rate A-Level' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$65',    'First lesson 32% off',  true  ],
            [ '8 lessons',    '$734',   '4% package saving',     false ],
            [ '16 lessons',   '$1,368', '10% package saving',    false ],
        ],
        'faqs'             => [
            [ 'q' => 'Does Mr Chen teach O-Level Physics?',  'a' => 'He focuses on JC and IB. For O-Level, see Mr Chen Wei Jie.' ],
            [ 'q' => 'Does he provide notes?',               'a' => 'Yes. Condensed derivation sheets and exam-pattern summaries after each topic.' ],
        ],
    ],

    'ms-cheryl-lim' => [
        'short_name'       => 'Ms Cheryl',
        'credential_badge' => 'NIE-trained',
        'languages'        => 'English, Mandarin',
        'duration'         => '1.5h · 1-2x/week',
        'active_students'  => 16,
        'last_active'      => '1d',
        'demo_video_url'   => '',
        'philosophy'       => 'Language is built through listening, speaking, reading, and writing — in that order. I never skip oral before composition.',
        'about_paragraphs' => [
            'Ex-MOE language tutor with 9 years specialising in PSLE Chinese and English. Strong oral coaching and composition structure framework that gets results quickly.',
            'Ms Cheryl\'s students report marked improvement in oral confidence within 4 weeks and composition marks within 6 weeks.',
        ],
        'specializations'  => [
            [ 'PSLE Chinese oral',   'Expert' ],
            [ 'English composition', 'Strong' ],
            [ 'Bilingual PSLE',      'Specialist' ],
            [ 'Confidence building', 'Expert' ],
        ],
        'qualifications'   => [
            [ 'B.A. English Literature',  'NUS · 2007',             'Second Upper' ],
            [ 'NIE PGDE (Primary)',        'NIE · 2009',             'Cert ID: NIE-PGDE-2009-7712' ],
            [ 'MOE Teacher',              '2009–2018 · Primary',    'English / Chinese' ],
            [ 'Background Check',         'Annual update',          'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '98',  'Reviews' ],
            [ '9y',  'Teaching exp' ],
            [ '4.9', 'Avg rating' ],
            [ '91%', 'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$45',  'First lesson 36% off',  true  ],
            [ '8 lessons',    '$538', '4% package saving',     false ],
            [ '16 lessons',   '$1,008', '10% package saving',  false ],
        ],
        'faqs'             => [
            [ 'q' => 'Can Ms Cheryl teach both English and Chinese?',  'a' => 'Yes. Many parents book her for both. She schedules them on the same day where possible.' ],
            [ 'q' => 'How does oral coaching work?',                   'a' => 'Each session includes stimulus-based practice and timed responses with targeted feedback.' ],
        ],
    ],

    'mr-raj-kumar' => [
        'short_name'       => 'Mr Raj',
        'credential_badge' => 'Full-time tutor',
        'languages'        => 'English, Tamil',
        'duration'         => '2h · 1-2x/week',
        'active_students'  => 15,
        'last_active'      => '2d',
        'demo_video_url'   => '',
        'philosophy'       => 'Science becomes obvious when you connect it to things students already see. Real-world examples first, theory second.',
        'about_paragraphs' => [
            'Engaging Science tutor known for real-world demonstrations and analogies. 92% of students improve by at least one grade within 3 months.',
            'Mr Raj covers Secondary Science, Chemistry, and Biology with a strong focus on structured answering techniques for structured questions.',
        ],
        'specializations'  => [
            [ 'Sec Science',       '92% improvement' ],
            [ 'O-Level Chemistry', 'Expert' ],
            [ 'Biology',           'Strong' ],
            [ 'Structured answers', 'Specialist' ],
        ],
        'qualifications'   => [
            [ 'B.Sc. Biochemistry',    'NUS · 2013',         'Second Upper' ],
            [ 'Full-time Tutor',       'Since 2017',          'Margick verified' ],
            [ 'Background Check',      'Annual update',       'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '76',  'Reviews' ],
            [ '7y',  'Tutoring exp' ],
            [ '92%', 'Grade improvement' ],
            [ '87%', 'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$40',  'First lesson 33% off',  true  ],
            [ '8 lessons',    '$462', '4% package saving',     false ],
            [ '16 lessons',   '$864', '10% package saving',    false ],
        ],
        'faqs'             => [
            [ 'q' => 'Which exam board does Mr Raj cover?',  'a' => 'O-Level (Sec 3–4) for all Science subjects. He also covers lower secondary combined Science.' ],
            [ 'q' => 'Does he provide notes?',               'a' => 'Yes. He provides structured-question answer frameworks and summary notes per topic.' ],
        ],
    ],

    'ms-sarah-tan' => [
        'short_name'       => 'Ms Sarah',
        'credential_badge' => 'Ex-editor',
        'languages'        => 'English',
        'duration'         => '1.5h · 1-2x/week',
        'active_students'  => 13,
        'last_active'      => '2d',
        'demo_video_url'   => '',
        'philosophy'       => 'A strong essay always has three things: a clear point, specific evidence, and an honest explanation of what it means.',
        'about_paragraphs' => [
            'Former newspaper editor turned English tutor. Specialises in comprehension, essay writing, and oral exam preparation for O-Level students.',
            'Ms Sarah brings a professional writer\'s eye to marking student compositions — she identifies weak arguments and unclear sentences quickly and explains exactly how to fix them.',
        ],
        'specializations'  => [
            [ 'O-Level Essay writing', 'Expert' ],
            [ 'Comprehension',         'Strong' ],
            [ 'Oral exam prep',        'Expert' ],
            [ 'Sec 1-2 foundation',    'Strong' ],
        ],
        'qualifications'   => [
            [ 'B.A. Mass Communication', 'NTU · 2012',     'Second Upper' ],
            [ 'Newspaper editor',        '2012–2019',      'English language editing' ],
            [ 'Full-time Tutor',         'Since 2020',     'Margick verified' ],
            [ 'Background Check',        'Annual update',  'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '89',  'Reviews' ],
            [ '6y',  'Tutoring exp' ],
            [ '4.7', 'Avg rating' ],
            [ '85%', 'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$35',  'First lesson 36% off',  true  ],
            [ '8 lessons',    '$424', '4% package saving',     false ],
            [ '16 lessons',   '$800', '9% package saving',     false ],
        ],
        'faqs'             => [
            [ 'q' => 'Does Ms Sarah teach Primary English?',  'a' => 'No. She focuses on Secondary English (Sec 1–4, O-Level).' ],
            [ 'q' => 'Does she mark essays between lessons?',  'a' => 'Yes. She reviews one student essay per week and returns annotated feedback within 24 hours.' ],
        ],
    ],

    'mr-lim-boon-kiat' => [
        'short_name'       => 'Mr Lim',
        'credential_badge' => 'Ex-JC Lecturer',
        'languages'        => 'English, Mandarin',
        'duration'         => '2h · 2x/week',
        'active_students'  => 10,
        'last_active'      => '1d',
        'demo_video_url'   => '',
        'philosophy'       => 'JC Math rewards students who can hold 10 concepts in their head at once. I build that capacity deliberately over 4 months.',
        'about_paragraphs' => [
            'Former JC lecturer at Hwa Chong Institution with 15 years of A-Level Math experience. Now tutoring privately with a focus on H2 Math from first principles.',
            'Mr Lim is known for making abstract JC concepts concrete and for drilling exam patterns until students can answer under full time pressure.',
        ],
        'specializations'  => [
            [ 'H2 Math A-Level',    '90% A rate' ],
            [ 'Complex numbers',    'Expert' ],
            [ 'Statistics',         'Strong' ],
            [ 'Timed exam drills',  'Specialist' ],
        ],
        'qualifications'   => [
            [ 'M.Sc. Mathematics',        'NUS · 2005',             'Distinction' ],
            [ 'B.Sc. Mathematics (Hons)', 'NUS · 2003',             'First Class' ],
            [ 'JC Lecturer (HCI)',         '2006–2021 · H2 Math',   'Teaching record verified' ],
            [ 'Background Check',          'Annual update',         'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '67',  'Reviews' ],
            [ '15y', 'Teaching exp' ],
            [ '90%', 'H2 Math A rate' ],
            [ '96%', 'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$70',    'First lesson 30% off',  true  ],
            [ '8 lessons',    '$770',   '4% package saving',     false ],
            [ '16 lessons',   '$1,440', '10% package saving',    false ],
        ],
        'faqs'             => [
            [ 'q' => 'Does Mr Lim cover Pure or H1 Math?',   'a' => 'He focuses on H2 Math. For H1 or O-Level, see Mr Tan or Mr Xavier.' ],
            [ 'q' => 'How far in advance should I book?',    'a' => 'At least 2 weeks ahead as his schedule fills up fast. Trial slots open every Monday.' ],
        ],
    ],

    'ms-fatimah-binte-ali' => [
        'short_name'       => 'Ms Fatimah',
        'credential_badge' => 'Part-time tutor',
        'languages'        => 'English, Malay',
        'duration'         => '1h or 1.5h · 1-2x/week',
        'active_students'  => 8,
        'last_active'      => '4d',
        'demo_video_url'   => '',
        'philosophy'       => 'Young children learn best when they feel safe to make mistakes. I create that environment first, then build the skills.',
        'about_paragraphs' => [
            'Warm and encouraging tutor for young learners (P1–P4). Specialises in foundational English and Malay with a child-friendly teaching style.',
            'Ms Fatimah is patient, consistent, and particularly effective with children who have had negative experiences in school or are nervous about learning.',
        ],
        'specializations'  => [
            [ 'P1-P4 English foundation', 'Expert' ],
            [ 'Malay language',           'Specialist' ],
            [ 'Young learners',           'Expert' ],
            [ 'Anxious students',         'Strong' ],
        ],
        'qualifications'   => [
            [ 'B.A. Malay Studies', 'NUS · 2017',     'Second Upper' ],
            [ 'Part-time Tutor',    'Since 2021',      'Margick verified' ],
            [ 'Background Check',   'Annual update',  'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '33',  'Reviews' ],
            [ '4y',  'Tutoring exp' ],
            [ '4.6', 'Avg rating' ],
            [ '79%', 'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$25',  'First lesson 37% off',  true  ],
            [ '8 lessons',    '$308', '4% package saving',     false ],
            [ '12 lessons',   '$453', '6% package saving',     false ],
        ],
        'faqs'             => [
            [ 'q' => 'Does Ms Fatimah teach Secondary students?',  'a' => 'No. She focuses on P1–P4. For older students, see Ms Cheryl or Ms Sarah.' ],
            [ 'q' => 'Does she teach Malay at all levels?',        'a' => 'P1–P4 Malay. For PSLE or O-Level Malay, please enquire directly.' ],
        ],
    ],

    'mr-chen-wei-jie' => [
        'short_name'       => 'Mr Wei Jie',
        'credential_badge' => 'NUS-grad',
        'languages'        => 'English, Mandarin',
        'duration'         => '1.5h or 2h · 1-2x/week',
        'active_students'  => 7,
        'last_active'      => '3d',
        'demo_video_url'   => '',
        'philosophy'       => 'If a student cannot explain why the formula works, they are not ready for exam conditions. Understanding before speed.',
        'about_paragraphs' => [
            'NUS Physics undergraduate tutoring O-Level and A-Level Physics and A-Math. Makes complex concepts intuitive with step-by-step logical breakdowns.',
            'Mr Chen Wei Jie brings a current student\'s perspective — he knows exactly what examiners look for and keeps up with the latest syllabus changes.',
        ],
        'specializations'  => [
            [ 'O-Level Physics',   'Strong' ],
            [ 'A-Level H2 Physics', 'Expert' ],
            [ 'A-Math',             'Strong' ],
            [ 'Current syllabus',   'Up to date' ],
        ],
        'qualifications'   => [
            [ 'B.Sc. Physics (Hons)', 'NUS · Expected 2026',  'Undergraduate, year 3' ],
            [ 'Part-time Tutor',      'Since 2023',            'Margick verified' ],
            [ 'Background Check',     'Annual update',         'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '28',  'Reviews' ],
            [ '2y',  'Tutoring exp' ],
            [ '4.7', 'Avg rating' ],
            [ '81%', 'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$30',  'First lesson 40% off',  true  ],
            [ '8 lessons',    '$386', '4% package saving',     false ],
            [ '16 lessons',   '$720', '10% package saving',    false ],
        ],
        'faqs'             => [
            [ 'q' => 'Does Mr Wei Jie teach A-Level as well?',  'a' => 'Yes. He covers H2 Physics and is especially strong at topics shared with O-Level.' ],
            [ 'q' => 'Is he available on weekends?',             'a' => 'Yes. Saturday mornings and Sunday afternoons are his primary slots.' ],
        ],
    ],

    'ms-ho-mei-ling' => [
        'short_name'       => 'Ms Ho',
        'credential_badge' => 'IB Specialist',
        'languages'        => 'English, Mandarin',
        'duration'         => '2h · 2x/week',
        'active_students'  => 6,
        'last_active'      => '1d',
        'demo_video_url'   => '',
        'philosophy'       => 'IB rewards students who can think across boundaries. I teach the same concept from three different angles so no exam question can surprise you.',
        'about_paragraphs' => [
            'IB specialist with 13 years tutoring HL and SL across Math, Chemistry, and Biology. Works with students from international schools across Singapore.',
            'Ms Ho is particularly effective for students who find IB assessments different from what they studied in local school — she bridges that gap fast.',
        ],
        'specializations'  => [
            [ 'IB Math AA HL/SL',   'Expert' ],
            [ 'IB Chemistry HL/SL', 'Expert' ],
            [ 'IB Biology HL/SL',   'Strong' ],
            [ 'IA preparation',     'Specialist' ],
        ],
        'qualifications'   => [
            [ 'M.Sc. Chemistry',             'Imperial College London · 2008',  'Distinction' ],
            [ 'B.Sc. Mathematics & Biology', 'NUS · 2006',                     'Second Upper' ],
            [ 'IB Examiner (retired)',        '2011–2020',                      'IB Chemistry SL' ],
            [ 'Background Check',             'Annual update',                  'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '41',  'Reviews' ],
            [ '13y', 'IB exp' ],
            [ '5.0', 'Avg rating' ],
            [ '95%', 'Renewal rate' ],
            [ '7+',  'Avg IB score' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$90',    'First lesson 31% off',  true  ],
            [ '8 lessons',    '$1,000', '4% package saving',     false ],
            [ '16 lessons',   '$1,872', '10% package saving',    false ],
        ],
        'faqs'             => [
            [ 'q' => 'Does Ms Ho take A-Level students?',  'a' => 'She focuses on IB. For A-Level, see Mr Lim or Mr Wong.' ],
            [ 'q' => 'Can she help with the Math IA?',     'a' => 'Yes. IA planning and feedback is included in her package sessions.' ],
        ],
    ],

    'mr-xavier-loh' => [
        'short_name'       => 'Mr Xavier',
        'credential_badge' => 'Full-time tutor',
        'languages'        => 'English, Mandarin',
        'duration'         => '2h · 1-2x/week',
        'active_students'  => 14,
        'last_active'      => '2d',
        'demo_video_url'   => '',
        'philosophy'       => 'Sec Math is mostly pattern recognition. My job is to expose students to enough patterns early enough that nothing surprises them on exam day.',
        'about_paragraphs' => [
            'Exam-focused secondary Math tutor. Breaks down A-Math and E-Math into weekly study plans with clear short-term targets for each student.',
            'Mr Xavier is especially popular with Sec 3 students who feel lost after the jump from lower to upper secondary Math.',
        ],
        'specializations'  => [
            [ 'A-Math O-Level',       'Expert' ],
            [ 'E-Math',               'Expert' ],
            [ 'Sec 3 transition',     'Specialist' ],
            [ 'Weekly study plans',   'Strong' ],
        ],
        'qualifications'   => [
            [ 'B.Sc. Applied Math',  'SIT · 2016',    'Second Upper' ],
            [ 'Full-time Tutor',     'Since 2019',     'Margick verified' ],
            [ 'Background Check',    'Annual update',  'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '58',  'Reviews' ],
            [ '5y',  'Tutoring exp' ],
            [ '4.8', 'Avg rating' ],
            [ '88%', 'Renewal rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$35',  'First lesson 36% off',  true  ],
            [ '8 lessons',    '$424', '4% package saving',     false ],
            [ '16 lessons',   '$800', '9% package saving',     false ],
        ],
        'faqs'             => [
            [ 'q' => 'Does Mr Xavier cover both A-Math and E-Math?',  'a' => 'Yes. Most of his students take both, and he schedules them together where possible.' ],
            [ 'q' => 'Does he provide weekly plans?',                  'a' => 'Yes. Each student gets a term plan broken into weekly targets with checkpoints.' ],
        ],
    ],

    'ms-patricia-ng' => [
        'short_name'       => 'Ms Patricia',
        'credential_badge' => 'NIE-trained',
        'languages'        => 'English, Mandarin, Cantonese',
        'duration'         => '1.5h or 2h · 1-2x/week',
        'active_students'  => 17,
        'last_active'      => '1d',
        'demo_video_url'   => '',
        'philosophy'       => 'Chinese is not just a subject — it is a living language. I teach students to use it, not just pass the test.',
        'about_paragraphs' => [
            'Dedicated Chinese language tutor with 16 years and MOE training. Proven oral and composition coaching for PSLE and O-Level Chinese.',
            'Ms Patricia is especially effective with students who grew up speaking mainly English at home and need to close the gap quickly before national exams.',
        ],
        'specializations'  => [
            [ 'PSLE Chinese',       'Expert' ],
            [ 'O-Level Chinese',    'Expert' ],
            [ 'Oral coaching',      'Specialist' ],
            [ 'Higher Chinese',     'Strong' ],
        ],
        'qualifications'   => [
            [ 'B.A. Chinese Studies (Hons)', 'NUS · 2002',         'First Class' ],
            [ 'NIE PGDE (Primary)',           'NIE · 2004',         'Cert ID: NIE-PGDE-2004-1199' ],
            [ 'MOE Teacher',                  '2004–2020',          'Primary and Sec Chinese' ],
            [ 'Background Check',             'Annual update',      'Last check: Jan 2026' ],
        ],
        'track_stats'      => [
            [ '115', 'Reviews' ],
            [ '16y', 'Teaching exp' ],
            [ '4.9', 'Avg rating' ],
            [ '93%', 'Renewal rate' ],
            [ '89%', 'PSLE A/A* rate' ],
        ],
        'packages'         => [
            [ 'Trial lesson', '$55',  'First lesson 31% off',  true  ],
            [ '8 lessons',    '$616', '4% package saving',     false ],
            [ '16 lessons',   '$1,152', '10% package saving',  false ],
        ],
        'faqs'             => [
            [ 'q' => 'Does Ms Patricia also cover Higher Chinese?',   'a' => 'Yes. She covers both standard and Higher Chinese at Primary and Secondary level.' ],
            [ 'q' => 'How does she help English-dominant students?',  'a' => 'She starts with high-frequency vocabulary and oral patterns, building confidence before written work.' ],
        ],
    ],

];

// ── Apply profiles to posts ───────────────────────────────────────────────────

$updated = 0;

foreach ( $profiles as $slug => $data ) {
    $posts = get_posts( [
        'post_type'   => 'mg_teacher',
        'post_status' => 'publish',
        'name'        => $slug,
        'numberposts' => 1,
    ] );

    if ( ! $posts ) {
        echo "SKIP (not found): $slug\n";
        continue;
    }

    $id = $posts[0]->ID;

    // Phase 1 — simple fields
    update_post_meta( $id, 'mgk_short_name',       $data['short_name'] );
    update_post_meta( $id, 'mgk_credential_badge', $data['credential_badge'] );
    update_post_meta( $id, 'mgk_languages',        $data['languages'] );
    update_post_meta( $id, 'mgk_duration',         $data['duration'] );
    update_post_meta( $id, 'mgk_active_students',  $data['active_students'] );
    update_post_meta( $id, 'mgk_last_active',      $data['last_active'] );
    update_post_meta( $id, 'mgk_demo_video_url',   $data['demo_video_url'] );
    update_post_meta( $id, 'mgk_philosophy',       $data['philosophy'] );

    // Phase 2 — repeater fields via ACF update_field
    if ( function_exists( 'update_field' ) ) {
        update_field( 'mgk_about_paragraphs', array_map( fn($p) => [ 'content' => $p ], $data['about_paragraphs'] ), $id );
        update_field( 'mgk_specializations',  array_map( fn($r) => [ 'specialty' => $r[0], 'level' => $r[1] ], $data['specializations'] ), $id );
        update_field( 'mgk_qualifications',   array_map( fn($r) => [ 'title' => $r[0], 'description' => $r[1], 'cert_id' => $r[2] ], $data['qualifications'] ), $id );
        update_field( 'mgk_track_stats',      array_map( fn($r) => [ 'value' => $r[0], 'label' => $r[1] ], $data['track_stats'] ), $id );
        update_field( 'mgk_packages',         array_map( fn($r) => [ 'name' => $r[0], 'price' => $r[1], 'description' => $r[2], 'featured' => $r[3] ], $data['packages'] ), $id );
        update_field( 'mgk_faqs',             $data['faqs'], $id );
    } else {
        // Fallback: store as JSON in post_meta when ACF not available
        update_post_meta( $id, 'mgk_about_paragraphs', json_encode( $data['about_paragraphs'] ) );
        update_post_meta( $id, 'mgk_specializations',  json_encode( $data['specializations'] ) );
        update_post_meta( $id, 'mgk_qualifications',   json_encode( $data['qualifications'] ) );
        update_post_meta( $id, 'mgk_track_stats',      json_encode( $data['track_stats'] ) );
        update_post_meta( $id, 'mgk_packages',         json_encode( $data['packages'] ) );
        update_post_meta( $id, 'mgk_faqs',             json_encode( $data['faqs'] ) );
    }

    echo "Updated: {$posts[0]->post_title} (ID $id)\n";
    $updated++;
}

echo "\nDone. Updated $updated teachers.\n";
