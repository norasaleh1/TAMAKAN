-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 07, 2025 at 11:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tamakan`
--

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int(10) UNSIGNED NOT NULL,
  `educatorID` int(10) UNSIGNED NOT NULL,
  `topicID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id`, `educatorID`, `topicID`) VALUES
(1, 223, 1),
(2, 222, 2),
(3, 222, 3),
(4, 222, 4),
(6, 111, 1),
(7, 225, 1);

-- --------------------------------------------------------

--
-- Table structure for table `quizfeedback`
--

CREATE TABLE `quizfeedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `quizID` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comments` text DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizfeedback`
--

INSERT INTO `quizfeedback` (`id`, `quizID`, `rating`, `comments`, `date`) VALUES
(1, 1, 2, 'easy', '2025-10-01 00:11:15'),
(2, 1, 1, 'hard', '2025-10-09 00:12:26'),
(3, 1, 1, 'very hard', '2025-09-09 01:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `quizquestion`
--

CREATE TABLE `quizquestion` (
  `id` int(10) UNSIGNED NOT NULL,
  `quizID` int(10) UNSIGNED NOT NULL,
  `question` text NOT NULL,
  `questionFigureFileName` varchar(255) DEFAULT NULL,
  `answerA` varchar(500) NOT NULL,
  `answerB` varchar(500) NOT NULL,
  `answerC` varchar(500) NOT NULL,
  `answerD` varchar(500) NOT NULL,
  `correctAnswer` enum('A','B','C','D') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizquestion`
--

INSERT INTO `quizquestion` (`id`, `quizID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`) VALUES
(1, 1, 'You accessed an API and got 401. What’s the most accurate explanation and action?', NULL, '401 = “Forbidden” → Request Authorization permissi...', '401 = “Unauthorized” → Add/fix the Authentication', '401 = “Not Found” → Check the Endpoint URL', 'Bad Request” → Fix the Request syntax', 'B'),
(2, 1, 'In databases, what’s the difference between Primary Key and Unique Key?', NULL, 'Primary Key allows duplicates, while Unique Key does not.', 'Unique Key must always be a Foreign Key.', 'Primary Key does not allow NULL values, while Unique Key allows a single NULL.', 'Primary Key and Unique Key are completely identical.', 'C'),
(3, 1, 'What is the purpose of the Operating System (OS)?', NULL, 'Managing hardware resources and coordinating between hardware and software.', 'Running programs only without dealing with hardware.', 'Managing databases inside the machine.', 'It is basically an Internet browser.', 'A'),
(4, 1, 'In Operating Systems, which mechanism is used to allocate CPU time among processes?', NULL, 'Paging', 'Deadlock', 'Multiprogramming', 'Scheduling', 'D'),
(5, 1, 'In Data Structures, which structure implements FIFO (First In, First Out)?', NULL, 'Queue', 'Stack', 'Linked List', 'Tree', 'A'),
(6, 1, 'If an employee’s computer suddenly becomes very slow, what’s the first IT Support step?', NULL, 'Reinstall the OS immediately.', 'Check resource usage (CPU, RAM, Disk).', 'Replace the whole device.', 'Give them faster Internet.', 'B'),
(7, 1, 'If a database gives an error when inserting new data, what’s the first step?', NULL, 'Reinstall the database.', 'Check table constraints and the primary key.', 'Change the whole system.', 'Enter data into Excel instead.', 'B'),
(8, 1, 'If an employee asks you to install a program from the internet on a company device, what do you do?', NULL, 'Verify it is authorized and safe before installation.', 'Install it directly without checks.', 'Refuse all programs even if needed.', 'Let the employee install it themselves.', 'A'),
(9, 1, 'If the internal network keeps disconnecting intermittently, what’s the first step?', NULL, 'Check network devices (Router/Switch) and cabling.', 'Change users’ operating systems.', 'Shut down the internet completely.', 'Reformat all devices.', 'A'),
(10, 1, 'For a new project that needs a database, what’s the first thing to consider?', NULL, 'Identify requirements and data types.', 'Randomly pick any DBMS.', 'Start entering data before design.', 'Focus only on the interface design.', 'A'),
(11, 2, 'If an employee’s device is infected with Ransomware, what’s the first step you should take?', NULL, 'Restart the device and try to log in again.', 'Pay the ransom directly.', 'Isolate the device from the network immediately to prevent spreading.', 'Copy the malware to another device for testing.', 'C'),
(12, 2, 'If an attacker impersonates the identity of a device or user in the network, what type of attack is it?', NULL, 'Sniffing', 'Phishing', 'Spoofing', 'DoS', 'C'),
(13, 2, 'If an attacker intercepts communication between a user and a server and modifies the data, what attack is this?', NULL, 'Man-in-the-Middle (MITM)', 'Sniffing', 'Phishing', 'DDoS', 'A'),
(14, 2, 'If an attacker hides a malicious executable inside a normal program to open a backdoor, what attack is this?', NULL, 'Worm', 'Spyware', 'Trojan Horse', 'Adware', 'C'),
(15, 2, 'If the attack relies on trying millions of passwords until it succeeds, what type of attack is this?', NULL, 'Brute Force Attack', 'Phishing Attack', 'MITM', 'SQL Injection', 'A'),
(16, 2, 'If you notice suspicious logins to an employee’s account outside working hours, what’s the first step?', NULL, 'Ignore it since the account is still working.', 'Tell the employee to replace their device completely.', 'Delete all the account’s data.', 'Temporarily suspend the account and review logs.', 'D'),
(17, 2, 'If a user receives a message with a fake link that looks like the official site and asks for credentials, what type of attack is this?', NULL, 'Spoofing', 'Sniffing', 'DoS', 'Phishing', 'D'),
(18, 2, 'Which of these is commonly used for Two-Factor Authentication (2FA)?', NULL, 'Password only', 'Password + Fingerprint', 'VPN only', 'Username + Password', 'B'),
(19, 2, 'During a DDoS attack, what is most likely to be affected?', NULL, 'Network performance', 'Passwords', 'Encryption algorithms', 'User permissions', 'A'),
(20, 2, 'If you discover that passwords are stored in plain text in a database, what’s the issue?', NULL, 'No issue if the server is internal.', 'Critical security weakness – passwords must be hashed + salted.', 'Network security alone is enough.', 'Plain text is faster for performance.', 'B'),
(21, 3, 'What’s the difference between Revenue and Income?', NULL, 'Revenue = net profit; Income = total sales.', 'Revenue = sales – expenses; Income = total sales.', 'Revenue and Income are exactly the same.', 'Revenue = total sales; Income = net profit after costs.', 'D'),
(22, 3, 'If you find a large duplicate invoice in the system, what should you do?', NULL, 'Delete the invoice immediately without checking.', 'Verify the records and confirm with the vendor before making changes.', 'Record it as an extra expense to balance accounts.', 'Ignore it because the system is usually correct.', 'B'),
(23, 3, 'If a fixed asset was sold but the gain/loss wasn’t recorded, what’s the correct action?', NULL, 'Keep the asset as if it was never sold.', 'Record it as an operating expense.', 'Add it directly to capital account.', 'Record the gain or loss of the asset sale in the financial statements.', 'D'),
(24, 3, 'If salary expenses don’t match the actual number of employees, what should you do?', NULL, 'Review payroll and reconcile with HR records.', 'Change the number in the books to match the budget.', 'Deduct the difference from sales account.', 'Ignore because it’s not critical.', 'A'),
(25, 3, 'If the bank deducted a fee that isn’t recorded in the books, what’s the correct action?', NULL, 'Record it as a bank expense and reconcile.', 'Add it to revenue balance.', 'Leave it unrecorded.', 'Deduct it from supplier accounts.', 'A'),
(26, 3, 'If you find a difference between the company’s bank balance and the bank statement, what’s the first step?', NULL, 'Adjust the balance in the books directly.', 'Charge the difference to expenses.', 'Perform a bank reconciliation and compare each transaction.', 'Wait until year-end to see if it disappears.', 'C'),
(27, 3, 'If you discover a conflict of interest involving your audit colleague, what should you do?', NULL, 'Report it to the responsible authority and document the note.', 'Ignore because it’s not your business.', 'Cooperate with them to protect them.', 'Edit the report yourself without informing.', 'A'),
(28, 3, 'If a client overpays an invoice, what’s the correct action?', NULL, 'Record it as extra revenue for the company.', 'Record it as an advance payment or refund the amount.', 'Leave it as accounts receivable.', 'Add it to capital account.', 'B'),
(29, 3, 'If a supplier gives you an invoice without an issue date, what should you do?', NULL, 'Reject it and request a complete valid invoice.', 'Record it anyway since the amount is correct.', 'Add a date yourself.', 'Record it as a prepaid expense.', 'A'),
(30, 3, 'If the company hasn’t done a physical inventory for 2 years, what’s your position?', NULL, 'Request an urgent inventory count and reconcile with the books.', 'Ignore since inventory is recorded electronically.', 'Assume the numbers are always correct.', 'Record it as unrealized revenue.', 'A'),
(31, 4, 'If a diabetic patient comes with severe hypoglycemia, what’s the first step?', NULL, 'Give extra insulin.', 'Give IV Glucose immediately.', 'Wait until sugar rises naturally.', 'Advise the patient to drink only water.', 'B'),
(32, 4, 'If a child comes with fever and chronic cough, what’s the first thing you think of?', NULL, 'Simple cold', 'GERD (reflux)', 'Pneumonia', 'Food poisoning', 'C'),
(33, 4, 'If a patient comes with chest pain and shortness of breath, what’s the first action?', NULL, 'Perform an ECG (Electrocardiogram).', 'Do a random blood test.', 'Send directly to CT scan.', 'Give painkillers and wait.', 'A'),
(34, 4, 'If a patient has severe bleeding after an accident, what’s the most important first step?', NULL, 'Take X-rays before anything.', 'Control the bleeding.', 'Write a prescription for painkillers.', 'Wait for lab results.', 'B'),
(35, 4, 'If a patient has very high blood pressure, what’s the first appropriate action?', NULL, 'Let the patient rest until BP decreases.', 'Give antihypertensive medication and monitor closely.', 'Give large amounts of IV fluids.', 'Give insulin.', 'B'),
(36, 4, 'If a patient comes with severe abdominal pain, what’s the first step?', NULL, 'History & Physical Examination.', 'Give strong painkillers immediately.', 'Request surgery directly without evaluation.', 'Wait until the pain improves by itself.', 'A'),
(37, 4, 'If a patient suddenly has shortness of breath, what’s the first thing to check?', NULL, 'Airway & Breathing', 'Blood sugar level', 'Blood pressure only', 'Patient’s medical file', 'A'),
(38, 4, 'If a patient shows signs of severe dehydration (low BP, thirst, dizziness), what’s the first step?', NULL, 'Give IV fluids immediately.', 'Give painkillers.', 'Let the patient drink water only.', 'Wait until the patient recovers naturally.', 'A'),
(39, 4, 'If a patient develops a drug allergy after a dose, what’s the first step?', NULL, 'Continue the same dose and observe.', 'Switch the drug without reporting.', 'Give simple painkillers and continue treatment.', 'Stop the drug immediately, notify the doctor, and give Epinephrine if severe.', 'D'),
(40, 4, 'If a patient faints during blood donation, what’s the first step?', NULL, 'Keep the patient sitting until recovery.', 'Lay the patient down, elevate legs, and monitor breathing.', 'Give sedative medication.', 'Ignore because it’s always normal.', 'B');

-- --------------------------------------------------------

--
-- Table structure for table `recommendedquestion`
--

CREATE TABLE `recommendedquestion` (
  `id` int(10) UNSIGNED NOT NULL,
  `quizID` int(10) UNSIGNED NOT NULL,
  `learnerID` int(10) UNSIGNED NOT NULL,
  `question` text NOT NULL,
  `questionFigureFileName` varchar(255) DEFAULT NULL,
  `answerA` varchar(500) NOT NULL,
  `answerB` varchar(500) NOT NULL,
  `answerC` varchar(500) NOT NULL,
  `answerD` varchar(500) NOT NULL,
  `correctAnswer` enum('A','B','C','D') NOT NULL,
  `status` enum('pending','approved','disapproved') NOT NULL DEFAULT 'pending',
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recommendedquestion`
--

INSERT INTO `recommendedquestion` (`id`, `quizID`, `learnerID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`, `status`, `comments`) VALUES
(1, 1, 224, 'What is the primary purpose of creating user personas in UX design?', NULL, 'To replace usability testing', 'To understand the target users’ goals, behaviors, and needs', 'o determine the visual color palette of the interface', 'To increase the number of product features', 'B', 'pending', '');

-- --------------------------------------------------------

--
-- Table structure for table `takenquiz`
--

CREATE TABLE `takenquiz` (
  `id` int(10) UNSIGNED NOT NULL,
  `quizID` int(10) UNSIGNED NOT NULL,
  `score` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `takenquiz`
--

INSERT INTO `takenquiz` (`id`, `quizID`, `score`) VALUES
(1, 1, 5.00),
(2, 1, 4.00),
(3, 1, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `topic`
--

CREATE TABLE `topic` (
  `id` int(10) UNSIGNED NOT NULL,
  `topicName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topic`
--

INSERT INTO `topic` (`id`, `topicName`) VALUES
(3, 'Accounting'),
(2, 'Cybersecurity'),
(1, 'IT'),
(4, 'Medicine');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(10) UNSIGNED NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `emailAddress` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photoFileName` varchar(255) NOT NULL DEFAULT 'default.png',
  `userType` enum('learner','educator') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstName`, `lastName`, `emailAddress`, `password`, `photoFileName`, `userType`) VALUES
(111, 'sara', 'khalid', 'sara@gmail.com', '1234', 'default.png', 'educator'),
(222, 'maha', 'ahmed', 'maga@gmail.com', '1234', 'default.png', 'educator'),
(223, 'hala', 'belal', 'hala@gmail.com', '$2y$10$BAbChsT4R0wKMTP3DaUqMOEdmot15tjVswq.qdu0Q9yGgZCqZi/HS', 'default.png', 'educator'),
(224, 'mona', 'mohammed', 'mona@gmail.com', '$2y$10$DoHooSniLH89FpAvGF2pHOoKG5bUpBx9gMn7t/DqE/0QqCcDAAQZe', 'default.png', 'learner'),
(225, 'lena', 'ahmed', 'lena@gmail.com', '$2y$10$oHZeUde8zm4K8DLoNh0oGeOYnjTcx7.tKZ4uMfcH5sDRXGJE91SkS', 'default.png', 'educator');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_educator` (`educatorID`),
  ADD KEY `idx_quiz_topic` (`topicID`);

--
-- Indexes for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qf_quiz` (`quizID`);

--
-- Indexes for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qq_quiz` (`quizID`);

--
-- Indexes for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rq_quiz` (`quizID`),
  ADD KEY `idx_rq_learner` (`learnerID`);

--
-- Indexes for table `takenquiz`
--
ALTER TABLE `takenquiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tq_quiz` (`quizID`);

--
-- Indexes for table `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_topic_name` (`topicName`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_email` (`emailAddress`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quizquestion`
--
ALTER TABLE `quizquestion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `takenquiz`
--
ALTER TABLE `takenquiz`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `topic`
--
ALTER TABLE `topic`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=226;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `fk_quiz_topic` FOREIGN KEY (`topicID`) REFERENCES `topic` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quiz_user` FOREIGN KEY (`educatorID`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  ADD CONSTRAINT `fk_quizfeedback_quiz` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD CONSTRAINT `fk_quizquestion_quiz` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  ADD CONSTRAINT `fk_recommendedquestion_learner` FOREIGN KEY (`learnerID`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recommendedquestion_quiz` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `takenquiz`
--
ALTER TABLE `takenquiz`
  ADD CONSTRAINT `fk_takenquiz_quiz` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
