import base64
import os
import subprocess
import sys

def get_logo_base64():
    logo_path = "public/images/logo.png"
    if os.path.exists(logo_path):
        with open(logo_path, "rb") as f:
            return base64.b64encode(f.read()).decode("utf-8")
    return ""

def build_html(logo_b64):
    html = """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Information Management Database Reviewer - Barangay Banlic CCIRS</title>
<style>
  @page {
    size: A4 portrait;
    margin: 12mm 12mm 12mm 12mm;
  }
  * {
    box-sizing: border-box;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1e293b;
    line-height: 1.45;
    font-size: 10.8px;
    margin: 0;
    padding: 0;
    background-color: #ffffff;
  }

  /* Header & Banner */
  .doc-header {
    display: flex;
    align-items: center;
    gap: 14px;
    border-bottom: 2.5px solid #1e3a8a;
    padding-bottom: 9px;
    margin-bottom: 11px;
  }
  .doc-logo {
    width: 58px;
    height: 58px;
    object-fit: contain;
  }
  .header-meta {
    flex: 1;
  }
  .header-badge {
    display: inline-block;
    background: #dbeafe;
    color: #1e40af;
    font-size: 8.5px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
  }
  .doc-header h1 {
    font-size: 15.5px;
    font-weight: 800;
    margin: 0 0 2px 0;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    line-height: 1.25;
  }
  .doc-header p {
    margin: 0;
    font-size: 9.8px;
    color: #475569;
    line-height: 1.35;
  }

  /* Metric Highlights Grid */
  .meta-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 7px;
    margin-bottom: 11px;
  }
  .meta-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    padding: 6px 7px;
    text-align: center;
  }
  .meta-card .val {
    display: block;
    font-size: 11px;
    font-weight: 800;
    color: #1e3a8a;
    margin-bottom: 1px;
  }
  .meta-card .lbl {
    font-size: 8.5px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 700;
  }

  /* Section Headings */
  h2 {
    font-size: 12px;
    color: #0f172a;
    background: #f1f5f9;
    border-left: 4px solid #1e3a8a;
    padding: 4px 8px;
    margin: 13px 0 7px 0;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    font-weight: 800;
    break-after: avoid;
  }
  h3 {
    font-size: 11px;
    color: #1e3a8a;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 2px;
    margin: 9px 0 5px 0;
    font-weight: 700;
    break-after: avoid;
  }
  h4 {
    font-size: 10px;
    color: #334155;
    margin: 6px 0 3px 0;
    font-weight: 700;
    break-after: avoid;
  }
  p, li {
    color: #334155;
    font-size: 10.5px;
    margin: 0 0 4.5px 0;
    line-height: 1.45;
  }
  ul, ol {
    margin: 0 0 6px 0;
    padding-left: 16px;
  }
  li {
    margin-bottom: 2px;
  }

  /* Visual Architecture Cards */
  .tier-container {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin: 7px 0 9px 0;
    break-inside: avoid;
  }
  .tier-box {
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    padding: 6px 9px;
    background: #ffffff;
  }
  .tier-box-pres { border-left: 4px solid #3b82f6; }
  .tier-box-app  { border-left: 4px solid #10b981; }
  .tier-box-orm  { border-left: 4px solid #8b5cf6; }
  .tier-box-db   { border-left: 4px solid #f59e0b; }
  .tier-title {
    font-size: 10px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
  }
  .tier-desc {
    font-size: 9.5px;
    color: #475569;
    margin: 0;
  }
  .tier-arrow {
    text-align: center;
    font-size: 9px;
    color: #64748b;
    font-weight: bold;
    margin: -3px 0;
  }

  /* Callouts & Alert Boxes */
  .callout {
    padding: 6px 10px;
    margin: 7px 0;
    border-radius: 4px;
    font-size: 10.2px;
    line-height: 1.42;
    break-inside: avoid;
  }
  .callout-info {
    background: #eff6ff;
    border-left: 3.5px solid #3b82f6;
    color: #1e40af;
  }
  .callout-success {
    background: #f0fdf4;
    border-left: 3.5px solid #16a34a;
    color: #166534;
  }
  .callout-warning {
    background: #fefce8;
    border-left: 3.5px solid #eab308;
    color: #854d0e;
  }
  .callout-danger {
    background: #fef2f2;
    border-left: 3.5px solid #ef4444;
    color: #991b1b;
  }

  /* Data Tables */
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 6px 0 10px 0;
    font-size: 9.5px;
    break-inside: avoid;
  }
  th, td {
    padding: 4px 6px;
    text-align: left;
    border: 1px solid #cbd5e1;
  }
  th {
    background: #1e293b;
    color: #ffffff;
    font-weight: 700;
    font-size: 9.5px;
  }
  tr:nth-child(even) {
    background: #f8fafc;
  }
  .badge-pk {
    display: inline-block;
    background: #fee2e2;
    color: #991b1b;
    font-weight: 700;
    font-size: 8px;
    padding: 1px 4px;
    border-radius: 3px;
    border: 1px solid #f87171;
  }
  .badge-fk {
    display: inline-block;
    background: #e0e7ff;
    color: #3730a3;
    font-weight: 700;
    font-size: 8px;
    padding: 1px 4px;
    border-radius: 3px;
    border: 1px solid #818cf8;
  }
  .badge-idx {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    font-weight: 700;
    font-size: 8px;
    padding: 1px 4px;
    border-radius: 3px;
  }

  /* Code Blocks */
  pre, code {
    font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
  }
  code {
    background: #f1f5f9;
    color: #0f172a;
    padding: 1px 4px;
    border-radius: 3px;
    font-size: 9.2px;
  }
  pre {
    background: #0f172a;
    color: #f8fafc;
    padding: 6px 10px;
    border-radius: 5px;
    font-size: 8.8px;
    overflow-x: auto;
    margin: 4px 0 8px 0;
    line-height: 1.36;
    break-inside: avoid;
  }
  pre .cm { color: #94a3b8; font-style: italic; }
  pre .kw { color: #38bdf8; font-weight: bold; }
  pre .str { color: #4ade80; }
  pre .fn { color: #fbbf24; }
  pre .cls { color: #c084fc; }

  /* Structured ERD Domain Cards */
  .erd-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin: 6px 0 10px 0;
    break-inside: avoid;
  }
  .erd-card {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    padding: 7px 9px;
  }
  .erd-card h4 {
    margin: 0 0 4px 0;
    color: #1e3a8a;
    font-size: 10px;
    border-bottom: 1.5px solid #cbd5e1;
    padding-bottom: 2px;
  }
  .erd-table-item {
    font-size: 9.5px;
    margin-bottom: 2.5px;
  }
  .erd-table-item b { color: #0f172a; }
  .erd-table-item span { color: #64748b; font-size: 8.5px; }

  /* Defense QA Block */
  .qa-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-left: 3.5px solid #1e3a8a;
    border-radius: 4px;
    padding: 6px 9px;
    margin-bottom: 7px;
    break-inside: avoid;
  }
  .qa-q {
    font-weight: 700;
    color: #1e3a8a;
    font-size: 10.2px;
    margin-bottom: 2.5px;
    display: flex;
    align-items: baseline;
    gap: 5px;
  }
  .qa-q .q-tag {
    background: #1e3a8a;
    color: #ffffff;
    font-size: 7.8px;
    font-weight: 800;
    padding: 1px 4px;
    border-radius: 3px;
  }
  .qa-a {
    color: #334155;
    font-size: 9.8px;
    margin: 0;
    line-height: 1.42;
  }

  .chapter-break {
    page-break-before: always;
  }

  .footer-bar {
    margin-top: 10px;
    padding-top: 5px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    font-size: 8.2px;
    color: #64748b;
  }
</style>
</head>
<body>

<!-- PAGE 1: OVERVIEW & 3-TIER ARCHITECTURE -->
<div class="doc-header">
  <img src="data:image/png;base64,__LOGO_B64__" alt="Barangay Banlic Logo" class="doc-logo">
  <div class="header-meta">
    <div class="header-badge">ACADEMIC & TECHNICAL DEFENSE COMPENDIUM</div>
    <h1>Information Management: Database Architecture & System Integration Reviewer</h1>
    <p><b>System:</b> Community Complaint and Incident Reporting System (Banlic-CCIRS) &bull; <b>LGU:</b> Barangay Banlic, Calamba City, Laguna</p>
    <p><b>Course Scope:</b> Relational Modeling, 3NF Normalization, MySQL InnoDB Engine, Laravel Eloquent ORM, and ACID Transaction Flow</p>
  </div>
</div>

<div class="meta-grid">
  <div class="meta-card">
    <span class="val">MySQL 8 / MariaDB</span>
    <span class="lbl">RDBMS & Storage Engine (InnoDB)</span>
  </div>
  <div class="meta-card">
    <span class="val">16 Normalized Tables</span>
    <span class="lbl">Schema Structure (3NF / BCNF)</span>
  </div>
  <div class="meta-card">
    <span class="val">Laravel 11 / PHP 8.2+</span>
    <span class="lbl">Data Access Tier (Eloquent ORM)</span>
  </div>
  <div class="meta-card">
    <span class="val">Asia/Manila (UTC+8)</span>
    <span class="lbl">Temporal Consistency & Validation</span>
  </div>
</div>

<h2>1. Executive Overview & Course Context</h2>
<p>
  In <b>Information Management (IM)</b>, an enterprise database serves as the authoritative, persistent information backbone that models organizational business logic, maintains accountability, and powers operational reporting. In civic governance, the <b>Barangay Banlic Community Complaint and Incident Reporting System (Banlic-CCIRS)</b> automates incident logging, multi-committee assignment, investigation recording, resident ID verification, and formal dispute mediation (Katarungang Pambarangay).
</p>
<div class="callout callout-info">
  <b>Core Course Objective:</b> To understand how real-world civic entities (residents, grievances, committees, investigators) transform into a mathematical <b>Relational Schema (3NF)</b>, how data integrity is enforced via <b>referential constraints</b>, and how application software connects to the database via <b>Object-Relational Mapping (ORM)</b> and <b>ACID Transactions</b>.
</div>

<h3>1.1 Strategic Database Objectives in Local Governance</h3>
<ul>
  <li><b>Eliminating Information Silos:</b> Replaces disjointed paper logbooks and Excel sheets with a centralized MySQL database, creating a single source of truth across all barangay committees.</li>
  <li><b>Auditability & Non-Repudiation:</b> Chronologically tracks every dispute phase (Pending &rarr; Under Review &rarr; Assigned &rarr; In Progress &rarr; Resolved &rarr; Closed &rarr; Rejected) along with investigator remarks, actor IDs, and timestamps.</li>
  <li><b>Data Privacy & Access Control:</b> Enforces Role-Based Access Control (RBAC) separating administrative staff from residents, and complies with Republic Act 10173 (Philippine Data Privacy Act of 2012) via masked PII and salted bcrypt password hashing.</li>
  <li><b>Transactional Durability:</b> Utilizes the MySQL InnoDB storage engine with Write-Ahead Logging (WAL) and foreign key referential cascading to prevent data corruption and orphaned records.</li>
</ul>

<h2>2. System Architecture: The Multi-Tier Integration Pipeline</h2>
<p>
  The Banlic-CCIRS employs a classic <b>Three-Tier Client-Server Architecture</b> integrated with the <b>Model-View-Controller (MVC)</b> design pattern. This architecture enforces strict separation of concerns, ensuring that database schema changes do not break presentation templates, and user interface modifications never jeopardize data integrity.
</p>

<div class="tier-container">
  <div class="tier-box tier-box-pres">
    <div class="tier-title"><span>1. PRESENTATION TIER (CLIENT BROWSER)</span> <span>Blade Views &bull; Tailwind CSS &bull; Alpine.js</span></div>
    <p class="tier-desc">Renders responsive UI: Resident Filing Portal, Admin Dispatch Board, and Real-Time Tracking Timeline. Collects form inputs and dispatches HTTP POST/GET requests secured by CSRF tokens.</p>
  </div>
  <div class="tier-arrow">&darr; HTTP Request / CSRF Token Verification &darr;</div>

  <div class="tier-box tier-box-app">
    <div class="tier-title"><span>2. APPLICATION LOGIC TIER (LARAVEL 11 CONTROLLERS)</span> <span>Routing &bull; Middleware &bull; Business Validation</span></div>
    <p class="tier-desc">Authorizes user roles, enforces 5-minute clock drift grace tolerance (<code>before_or_equal:now + 5 mins</code>), generates unique reference numbers (<code>BRGY-YYYY-XXXX</code>), and orchestrates business workflows.</p>
  </div>
  <div class="tier-arrow">&darr; Model Invocation / Transaction Boundary (DB::transaction) &darr;</div>

  <div class="tier-box tier-box-orm">
    <div class="tier-title"><span>3. DATA ACCESS / ORM TIER (ELOQUENT &amp; PDO)</span> <span>Active Record Models &bull; PDO Prepared Statements</span></div>
    <p class="tier-desc">Translates PHP objects to relational SQL queries. Manages model relationships (<code>belongsTo</code>, <code>hasMany</code>, <code>hasOne</code>), attribute casting, and parameter binding preventing SQL injection.</p>
  </div>
  <div class="tier-arrow">&darr; TCP Port 3306 / Unix Socket (pdo_mysql) &darr;</div>

  <div class="tier-box tier-box-db">
    <div class="tier-title"><span>4. DATABASE TIER (MYSQL 8 / INNODB ENGINE)</span> <span>barangay_complaint_db &bull; utf8mb4_unicode_ci</span></div>
    <p class="tier-desc">Physical storage layer enforcing relational constraints, composite B+Tree indexes, AUTO_INCREMENT keys, row-level locking (MVCC), and Write-Ahead Logging (WAL) ensuring ACID durability.</p>
  </div>
</div>

<div class="callout callout-success">
  <b>Information Management Principle &bull; Data Independence:</b> <i>Physical data independence</i> is achieved because MySQL storage files (tablespaces, indexes, redo logs) can be migrated or tuned without altering application code. <i>Logical data independence</i> is preserved because controllers interact with abstract Eloquent models rather than raw physical table schemas.
</div>

<!-- PAGE 2: ENTITY-RELATIONSHIP MODEL & CARDINALITIES -->
<div class="chapter-break"></div>

<h2>3. Entity-Relationship (ER) Conceptual &amp; Logical Modeling</h2>
<p>
  The Banlic-CCIRS logical schema comprises <b>16 normalized relational tables</b> organized into four distinct operational domains: Reference Lookups, Identity & Access, Dispute Lifecycle, and Security & Audit Trails.
</p>

<div class="erd-grid">
  <div class="erd-card">
    <h4>Domain 1 &bull; Reference Lookups (Static Metadata)</h4>
    <div class="erd-table-item"><b>roles:</b> <span>(role_id, role_name, description)</span> &bull; 1:N with users</div>
    <div class="erd-table-item"><b>departments:</b> <span>(department_id, department_name, is_active)</span> &bull; Barangay offices/committees</div>
    <div class="erd-table-item"><b>areas:</b> <span>(area_id, area_name, is_active)</span> &bull; Puroks 1 to 5 geographic zones</div>
    <div class="erd-table-item"><b>report_types:</b> <span>(report_type_id, type_name)</span> &bull; Complaint, Concern, Incident</div>
    <div class="erd-table-item"><b>complaint_categories:</b> <span>(category_id, department_id, category_name)</span> &bull; Maps to depts</div>
    <div class="erd-table-item"><b>priority_levels:</b> <span>(priority_id, priority_name, sort_order)</span> &bull; Low to Urgent</div>
    <div class="erd-table-item"><b>complaint_statuses:</b> <span>(status_id, status_name, sort_order, badge_color)</span> &bull; 7 states</div>
  </div>

  <div class="erd-card">
    <h4>Domain 2 &bull; Identity &amp; Resident Verification</h4>
    <div class="erd-table-item"><b>users:</b> <span>(user_id, role_id, department_id, area_id, email, password_hash, verification_status, verified_by)</span> &bull; Core actor entity</div>
    <div class="erd-table-item"><b>area_staff:</b> <span>(area_id, user_id, assigned_at)</span> &bull; Associative/junction table for Purok officers (M:N)</div>
    <div class="erd-table-item"><b>password_resets:</b> <span>(reset_id, user_id, token_hash, expires_at)</span> &bull; 1:N with users</div>
    <div class="erd-table-item"><b>password_reset_tokens:</b> <span>(email, token, created_at)</span> &bull; Laravel auth reset</div>
  </div>

  <div class="erd-card">
    <h4>Domain 3 &bull; Core Dispute Operations</h4>
    <div class="erd-table-item"><b>complaints:</b> <span>(complaint_id, reference_no, complainant_id, type_id, category_id, priority_id, area_id, status_id, incident_date)</span> &bull; Central hub</div>
    <div class="erd-table-item"><b>complaint_assignments:</b> <span>(assignment_id, complaint_id, assigned_to, assigned_by, is_current)</span> &bull; Officer delegation trail</div>
    <div class="erd-table-item"><b>complaint_status_history:</b> <span>(history_id, complaint_id, old_status_id, new_status_id, changed_by)</span> &bull; Immutable timeline log</div>
    <div class="erd-table-item"><b>investigations:</b> <span>(investigation_id, complaint_id, investigator_id, findings)</span> &bull; Hearing evidence</div>
    <div class="erd-table-item"><b>resolutions:</b> <span>(resolution_id, complaint_id [UNIQUE], resolved_by)</span> &bull; Strict 1:1 settlement</div>
    <div class="erd-table-item"><b>attachments:</b> <span>(attachment_id, complaint_id, investigation_id, file_path)</span> &bull; Photos &amp; docs</div>
  </div>

  <div class="erd-card">
    <h4>Domain 4 &bull; Security &amp; Audit Trail</h4>
    <div class="erd-table-item"><b>audit_logs:</b> <span>(log_id, user_id, action, table_name, record_id, ip_address, created_at)</span> &bull; Non-repudiation ledger</div>
    <div class="erd-table-item"><b>notifications:</b> <span>(notification_id, user_id, complaint_id, message, is_read)</span> &bull; User notification bell</div>
  </div>
</div>

<h3>3.1 Cardinality &amp; Multiplicity Relationship Matrix</h3>
<table>
  <thead>
    <tr>
      <th style="width: 24%;">Parent Entity</th>
      <th style="width: 24%;">Child Entity</th>
      <th style="width: 14%;">Cardinality</th>
      <th style="width: 18%;">Foreign Key Column</th>
      <th style="width: 20%;">Referential Action</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>users</code> (Resident)</td>
      <td><code>complaints</code></td>
      <td><b>1 : N</b> (One-to-Many)</td>
      <td><code>complainant_id</code></td>
      <td>RESTRICT (Cannot delete user with active cases)</td>
    </tr>
    <tr>
      <td><code>complaints</code></td>
      <td><code>attachments</code></td>
      <td><b>1 : N</b> (One-to-Many)</td>
      <td><code>complaint_id</code></td>
      <td>CASCADE (Deleting complaint deletes files)</td>
    </tr>
    <tr>
      <td><code>complaints</code></td>
      <td><code>complaint_status_history</code></td>
      <td><b>1 : N</b> (One-to-Many)</td>
      <td><code>complaint_id</code></td>
      <td>CASCADE (History purged on complaint delete)</td>
    </tr>
    <tr>
      <td><code>complaints</code></td>
      <td><code>complaint_assignments</code></td>
      <td><b>1 : N</b> (One-to-Many)</td>
      <td><code>complaint_id</code></td>
      <td>CASCADE (Assignments purged on delete)</td>
    </tr>
    <tr>
      <td><code>complaints</code></td>
      <td><code>resolutions</code></td>
      <td><b>1 : 1</b> (One-to-One)</td>
      <td><code>complaint_id (UNIQUE)</code></td>
      <td>CASCADE (Resolution purged on complaint delete)</td>
    </tr>
    <tr>
      <td><code>areas</code> &amp; <code>users</code></td>
      <td><code>area_staff</code></td>
      <td><b>M : N</b> (Many-to-Many)</td>
      <td><code>area_id</code>, <code>user_id</code></td>
      <td>CASCADE on both foreign keys (Junction table)</td>
    </tr>
    <tr>
      <td><code>departments</code></td>
      <td><code>complaint_categories</code></td>
      <td><b>1 : N</b> (One-to-Many)</td>
      <td><code>department_id</code></td>
      <td>SET NULL (Category stays active if dept changes)</td>
    </tr>
    <tr>
      <td><code>departments</code></td>
      <td><code>users</code> (Staff)</td>
      <td><b>1 : N</b> (One-to-Many)</td>
      <td><code>department_id</code></td>
      <td>SET NULL (Staff reassigned if dept dissolves)</td>
    </tr>
  </tbody>
</table>

<!-- PAGE 3: NORMALIZATION THEORY -->
<div class="chapter-break"></div>

<h2>4. Relational Database Theory &amp; Normalization Analysis</h2>
<p>
  <b>Database Normalization</b> is the formal mathematical process of decomposing complex table structures to minimize redundancy and eradicate <b>Insertion, Deletion, and Update Anomalies</b>. The Banlic-CCIRS schema is certified in <b>Third Normal Form (3NF)</b> with Boyce-Codd (BCNF) compliance.
</p>

<h3>4.1 First Normal Form (1NF) Proof &amp; Verification</h3>
<ul>
  <li><b>Atomicity of Attributes:</b> Every column contains strictly non-divisible, scalar values. Resident names are separated into <code>first_name</code>, <code>middle_name</code>, and <code>last_name</code> rather than a composite string.</li>
  <li><b>Elimination of Repeating Groups:</b> In unnormalized spreadsheets, multiple evidence photos or status remarks are saved as comma-separated lists (e.g., <code>"img1.jpg, img2.jpg"</code>). Banlic-CCIRS decouples evidence into the dedicated child table <code>attachments</code> and timeline events into <code>complaint_status_history</code>.</li>
  <li><b>Unique Tuple Identification:</b> Every table possesses an unambiguous Primary Key (e.g., <code>complaint_id</code>, <code>user_id</code>).</li>
</ul>

<h3>4.2 Second Normal Form (2NF) Proof &amp; Verification</h3>
<ul>
  <li><b>Prerequisite:</b> The relation is in 1NF.</li>
  <li><b>No Partial Functional Dependencies:</b> Every non-prime attribute must depend on the <i>entire</i> primary key, not a subset.</li>
  <li><b>Case Study &bull; <code>area_staff</code> Table:</b> The table uses a <b>composite primary key</b>: <code>(area_id, user_id)</code>. The attribute <code>assigned_at</code> depends strictly on the combination of <i>both</i> the area and the designated officer, not solely on one column. All single-column primary key tables automatically satisfy 2NF because partial dependency cannot exist on a single key.</li>
</ul>

<h3>4.3 Third Normal Form (3NF) Proof &amp; Verification</h3>
<ul>
  <li><b>Prerequisite:</b> The relation is in 2NF.</li>
  <li><b>No Transitive Dependencies:</b> Non-prime attributes must not depend on other non-prime attributes (X &rarr; Y &rarr; Z). Attributes must depend <i>only on the primary key</i>.</li>
  <li><b>Case Study &bull; Decoupling Departments &amp; Categories:</b> A poorly designed schema might place <code>department_name</code> and <code>department_head</code> directly inside the <code>complaints</code> table. If a department changes its name, updating thousands of complaint rows creates an <b>Update Anomaly</b>. Deleting all complaints in an area would wipe out department data (<b>Deletion Anomaly</b>). In Banlic-CCIRS:
    <br><code>complaint_id &rarr; category_id</code> and <code>category_id &rarr; department_id</code>.
    <br>By separating <code>departments</code>, <code>complaint_categories</code>, <code>priority_levels</code>, and <code>complaint_statuses</code> into lookup tables, all transitive dependencies are eliminated.</li>
</ul>

<div class="callout callout-warning">
  <b>Defense High-Yield Question:</b> <i>"Why does the complaints table still store status_id when complaint_status_history already logs status changes? Isn't that redundant?"</i><br>
  <b>Model Answer:</b> It is <b>controlled operational denormalization</b>. Keeping the active <code>status_id</code> on <code>complaints</code> allows O(1) indexed querying on the admin dashboard (<code>WHERE status_id = 1</code>) without executing an expensive aggregate subquery (<code>SELECT MAX(changed_at) FROM complaint_status_history GROUP BY complaint_id</code>) across millions of historical records. Synchronization is guaranteed via atomic <b>database transactions</b>.
</div>

<!-- PAGE 4: DATA DICTIONARY PART 1 -->
<div class="chapter-break"></div>

<h2>5. Comprehensive Data Dictionary (Metadata Catalog)</h2>
<p>
  The <b>Data Dictionary</b> catalogs physical column specifications, storage domains, nullability, keys, and relational constraints across the system's operational tables.
</p>

<h3>5.1 Core Entity: <code>complaints</code> Table</h3>
<table>
  <thead>
    <tr>
      <th style="width: 17%;">Column Name</th>
      <th style="width: 15%;">Data Type</th>
      <th style="width: 9%;">Null?</th>
      <th style="width: 14%;">Key / Index</th>
      <th style="width: 45%;">Description &amp; Integrity Constraints</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>complaint_id</code></td>
      <td>INT UNSIGNED</td>
      <td>NO</td>
      <td><span class="badge-pk">PRIMARY</span></td>
      <td>Surrogate primary key, AUTO_INCREMENT. Internal database identifier.</td>
    </tr>
    <tr>
      <td><code>reference_no</code></td>
      <td>VARCHAR(30)</td>
      <td>NO</td>
      <td>UNIQUE</td>
      <td>Format: <code>BRGY-YYYY-XXXX</code>. Public identifier used in URLs &amp; resident tracking.</td>
    </tr>
    <tr>
      <td><code>complainant_id</code></td>
      <td>INT UNSIGNED</td>
      <td>NO</td>
      <td><span class="badge-fk">FK users</span></td>
      <td>References <code>users(user_id)</code>. Foreign key linking to resident filer.</td>
    </tr>
    <tr>
      <td><code>report_type_id</code></td>
      <td>TINYINT UNSIGNED</td>
      <td>NO</td>
      <td><span class="badge-fk">FK report_types</span></td>
      <td>References <code>report_types(report_type_id)</code> (Complaint, Concern, Incident).</td>
    </tr>
    <tr>
      <td><code>category_id</code></td>
      <td>INT UNSIGNED</td>
      <td>NO</td>
      <td><span class="badge-fk">FK categories</span></td>
      <td>References <code>complaint_categories(category_id)</code>. Indexed: <code>idx_comp_category</code>.</td>
    </tr>
    <tr>
      <td><code>priority_id</code></td>
      <td>TINYINT UNSIGNED</td>
      <td>NO</td>
      <td><span class="badge-fk">FK priorities</span></td>
      <td>References <code>priority_levels(priority_id)</code> (Low, Medium, High, Urgent).</td>
    </tr>
    <tr>
      <td><code>area_id</code></td>
      <td>INT UNSIGNED</td>
      <td>NO</td>
      <td><span class="badge-fk">FK areas</span></td>
      <td>References <code>areas(area_id)</code> (Purok 1-5). Indexed: <code>idx_comp_area</code>.</td>
    </tr>
    <tr>
      <td><code>status_id</code></td>
      <td>TINYINT UNSIGNED</td>
      <td>NO</td>
      <td><span class="badge-fk">FK statuses</span></td>
      <td>References <code>complaint_statuses(status_id)</code>. Indexed: <code>idx_comp_status</code>.</td>
    </tr>
    <tr>
      <td><code>title</code></td>
      <td>VARCHAR(150)</td>
      <td>NO</td>
      <td>None</td>
      <td>Headline or summary statement describing the dispute.</td>
    </tr>
    <tr>
      <td><code>description</code></td>
      <td>TEXT</td>
      <td>NO</td>
      <td>None</td>
      <td>Detailed factual narrative submitted by the resident.</td>
    </tr>
    <tr>
      <td><code>location_details</code></td>
      <td>VARCHAR(255)</td>
      <td>YES</td>
      <td>None</td>
      <td>Specific street number, house landmark, or zone coordinates within Purok.</td>
    </tr>
    <tr>
      <td><code>incident_date</code></td>
      <td>DATETIME</td>
      <td>YES</td>
      <td>None</td>
      <td>Incident timestamp. Validated: &le; server time + 5-minute clock drift tolerance.</td>
    </tr>
    <tr>
      <td><code>created_at</code></td>
      <td>TIMESTAMP</td>
      <td>NO</td>
      <td><span class="badge-idx">INDEX</span></td>
      <td>Submission timestamp (Default: CURRENT_TIMESTAMP). Indexed: <code>idx_comp_created</code>.</td>
    </tr>
    <tr>
      <td><code>updated_at</code></td>
      <td>TIMESTAMP</td>
      <td>NO</td>
      <td>None</td>
      <td>Auto-updated timestamp on record modification via ON UPDATE CURRENT_TIMESTAMP.</td>
    </tr>
  </tbody>
</table>

<!-- PAGE 5: DATA DICTIONARY PART 2 -->
<div class="chapter-break"></div>

<h3>5.2 Identity &amp; Resident Verification: <code>users</code> Table</h3>
<table>
  <thead>
    <tr>
      <th style="width: 17%;">Column Name</th>
      <th style="width: 15%;">Data Type</th>
      <th style="width: 9%;">Null?</th>
      <th style="width: 14%;">Key / Index</th>
      <th style="width: 45%;">Description &amp; Integrity Constraints</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>user_id</code></td>
      <td>INT UNSIGNED</td>
      <td>NO</td>
      <td><span class="badge-pk">PRIMARY</span></td>
      <td>Surrogate primary key, AUTO_INCREMENT. System-wide actor ID.</td>
    </tr>
    <tr>
      <td><code>role_id</code></td>
      <td>TINYINT UNSIGNED</td>
      <td>NO</td>
      <td><span class="badge-fk">FK roles</span></td>
      <td>1 = Admin (Barangay Staff/Lupon), 2 = Complainant (Resident).</td>
    </tr>
    <tr>
      <td><code>department_id</code></td>
      <td>INT UNSIGNED</td>
      <td>YES</td>
      <td><span class="badge-fk">FK depts</span></td>
      <td>ON DELETE SET NULL. Assigned barangay committee for administrative users.</td>
    </tr>
    <tr>
      <td><code>committee_position</code></td>
      <td>VARCHAR(100)</td>
      <td>YES</td>
      <td>None</td>
      <td>Designated rank (e.g., Committee Chairman, Investigator, Lupon Member).</td>
    </tr>
    <tr>
      <td><code>area_id</code></td>
      <td>INT UNSIGNED</td>
      <td>YES</td>
      <td><span class="badge-fk">FK areas</span></td>
      <td>ON DELETE SET NULL. Resident's registered home Purok.</td>
    </tr>
    <tr>
      <td><code>first_name</code>, <code>last_name</code></td>
      <td>VARCHAR(60)</td>
      <td>NO</td>
      <td>None</td>
      <td>Legal names. Masked on public interfaces to uphold RA 10173 privacy.</td>
    </tr>
    <tr>
      <td><code>email</code></td>
      <td>VARCHAR(120)</td>
      <td>NO</td>
      <td>UNIQUE</td>
      <td>Login credential and notification destination address.</td>
    </tr>
    <tr>
      <td><code>password_hash</code></td>
      <td>VARCHAR(255)</td>
      <td>NO</td>
      <td>None</td>
      <td>One-way cryptographic hash generated via bcrypt (Cost Factor: 12).</td>
    </tr>
    <tr>
      <td><code>verification_status</code></td>
      <td>VARCHAR(20)</td>
      <td>NO</td>
      <td>None</td>
      <td>Resident ID review state: <code>pending</code>, <code>approved</code>, <code>rejected</code>.</td>
    </tr>
    <tr>
      <td><code>verification_doc_path</code></td>
      <td>VARCHAR(255)</td>
      <td>YES</td>
      <td>None</td>
      <td>Filesystem storage path for uploaded government ID or barangay certificate.</td>
    </tr>
    <tr>
      <td><code>verified_by</code></td>
      <td>INT UNSIGNED</td>
      <td>YES</td>
      <td><span class="badge-fk">FK users</span></td>
      <td>ON DELETE SET NULL. Self-referencing FK linking to admin reviewer.</td>
    </tr>
  </tbody>
</table>

<h3>5.3 Supporting Operational Tables</h3>
<table>
  <thead>
    <tr>
      <th style="width: 22%;">Table Name</th>
      <th style="width: 20%;">Primary Key</th>
      <th style="width: 28%;">Foreign Key Relationships</th>
      <th style="width: 30%;">Functional Role in System</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>complaint_assignments</code></td>
      <td><code>assignment_id</code></td>
      <td><code>complaint_id</code> (CASCADE), <code>assigned_to</code> (users), <code>assigned_by</code> (users)</td>
      <td>Delegates complaints to staff. <code>is_current=true</code> flags the active officer.</td>
    </tr>
    <tr>
      <td><code>complaint_status_history</code></td>
      <td><code>history_id</code></td>
      <td><code>complaint_id</code> (CASCADE), <code>old_status_id</code>, <code>new_status_id</code>, <code>changed_by</code></td>
      <td>Immutable chronological ledger powering the public progress timeline.</td>
    </tr>
    <tr>
      <td><code>investigations</code></td>
      <td><code>investigation_id</code></td>
      <td><code>complaint_id</code> (CASCADE), <code>investigator_id</code> (users)</td>
      <td>Field investigation findings, witness testimonies, and hearing notes.</td>
    </tr>
    <tr>
      <td><code>resolutions</code></td>
      <td><code>resolution_id</code></td>
      <td><code>complaint_id</code> (UNIQUE, CASCADE), <code>resolved_by</code> (users)</td>
      <td>1:1 settlement agreement, mediation terms, or formal closure minutes.</td>
    </tr>
    <tr>
      <td><code>attachments</code></td>
      <td><code>attachment_id</code></td>
      <td><code>complaint_id</code> (CASCADE), <code>investigation_id</code> (SET NULL), <code>uploaded_by</code></td>
      <td>Metadata catalog for photos, CCTV video, and affidavit documents.</td>
    </tr>
    <tr>
      <td><code>notifications</code></td>
      <td><code>notification_id</code></td>
      <td><code>user_id</code> (CASCADE), <code>complaint_id</code> (CASCADE)</td>
      <td>Real-time alert messages driving the resident's navbar notification bell.</td>
    </tr>
    <tr>
      <td><code>audit_logs</code></td>
      <td><code>log_id</code> (BIGINT)</td>
      <td><code>user_id</code> (SET NULL)</td>
      <td>Security audit ledger logging user IP addresses, actions, and altered tables.</td>
    </tr>
  </tbody>
</table>

<!-- PAGE 6: HOW THE DATABASE CONNECTS -->
<div class="chapter-break"></div>

<h2>6. How the Database Connects to the System</h2>
<p>
  A frequent question in defense hearings is: <i>"How does your Laravel code establish a physical connection to MySQL and execute queries?"</i> In Banlic-CCIRS, this lifecycle flows through configuration parsing, PDO driver management, schema migrations, and the Eloquent Active Record pattern.
</p>

<h3>6.1 Connection Pipeline: <code>.env</code> &rarr; <code>config/database.php</code> &rarr; PDO</h3>
<p>
  During framework bootstrapping, Laravel reads environment variables from <code>.env</code> and instantiates a <code>PDO</code> connection pool using the MySQL driver configuration:
</p>

<pre><span class="cm">// .env - Environment Variables</span>
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=barangay_complaint_db
DB_USERNAME=root
DB_PASSWORD=secret</pre>

<pre><span class="cm">// config/database.php - Driver Specification</span>
'connections' =&gt; [
    'mysql' =&gt; [
        'driver'    =&gt; 'mysql',
        'host'      =&gt; env('DB_HOST', '127.0.0.1'),
        'port'      =&gt; env('DB_PORT', '3306'),
        'database'  =&gt; env('DB_DATABASE', 'barangay_complaint_db'),
        'username'  =&gt; env('DB_USERNAME', 'root'),
        'password'  =&gt; env('DB_PASSWORD', ''),
        'charset'   =&gt; 'utf8mb4',
        'collation' =&gt; 'utf8mb4_unicode_ci',
        'strict'    =&gt; true,     <span class="cm">// Enforces strict SQL rules (rejects invalid dates and unsafe grouping)</span>
        'engine'    =&gt; 'InnoDB',  <span class="cm">// Guarantees transactional ACID support</span>
    ],
],</pre>

<h3>6.2 Schema Evolution via Laravel Migrations</h3>
<p>
  Instead of importing unversioned raw SQL dumps manually, table creation and column adjustments are defined in <b>Laravel Migrations</b> (e.g., <code>database/migrations/2026_01_01_000001_create_barangay_tables.php</code>). This provides repeatable schema deployment and rollback capability:
</p>

<pre><span class="cm">// Defining complaints table schema via Schema Builder</span>
Schema::create('complaints', function (Blueprint $table) {
    $table-&gt;increments('complaint_id');                          <span class="cm">// INT UNSIGNED AUTO_INCREMENT PK</span>
    $table-&gt;string('reference_no', 30)-&gt;unique();               <span class="cm">// Unique Candidate Key</span>
    $table-&gt;unsignedInteger('complainant_id');
    $table-&gt;unsignedTinyInteger('status_id')-&gt;index();        <span class="cm">// Secondary Index on Status</span>
    $table-&gt;text('description');
    $table-&gt;dateTime('incident_date')-&gt;nullable();
    $table-&gt;timestamps();

    <span class="cm">// Foreign Key Constraints enforcing Referential Integrity</span>
    $table-&gt;foreign('complainant_id')-&gt;references('user_id')-&gt;on('users');
    $table-&gt;foreign('status_id')-&gt;references('status_id')-&gt;on('complaint_statuses');
});</pre>

<!-- PAGE 7: ELOQUENT ORM & ACID THEORY -->
<div class="chapter-break"></div>

<h3>6.3 Object-Relational Mapping (Eloquent ORM) Deep Dive</h3>
<p>
  Eloquent maps each relational table to a PHP class implementing the <b>Active Record Pattern</b>. Below is the production implementation in <code>app/Models/Complaint.php</code>:
</p>

<pre><span class="cm">// app/Models/Complaint.php - Eloquent Model Mapping</span>
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $primaryKey = 'complaint_id';  <span class="cm">// Explicitly binds non-default PK column</span>
    protected $guarded = [];                 <span class="cm">// Mass assignment configuration</span>

    protected function casts(): array {
        return ['incident_date' =&gt; 'datetime']; <span class="cm">// Auto-converts SQL DATETIME to Carbon instance</span>
    }

    <span class="cm">// Conceals internal auto-increment ID from URLs; binds route parameters to reference_no</span>
    public function getRouteKeyName() { return 'reference_no'; }

    <span class="cm">// Declarative Relational Mappings</span>
    public function complainant()      { return $this-&gt;belongsTo(User::class, 'complainant_id', 'user_id'); }
    public function status()           { return $this-&gt;belongsTo(ComplaintStatus::class, 'status_id', 'status_id'); }
    public function attachments()      { return $this-&gt;hasMany(Attachment::class, 'complaint_id', 'complaint_id'); }
    public function history()          { return $this-&gt;hasMany(StatusHistory::class, 'complaint_id', 'complaint_id'); }
    public function resolution()       { return $this-&gt;hasOne(Resolution::class, 'complaint_id', 'complaint_id'); }
    public function currentAssignment() { 
        return $this-&gt;hasOne(ComplaintAssignment::class, 'complaint_id', 'complaint_id')
                    -&gt;where('is_current', true)-&gt;latestOfMany('assignment_id'); 
    }
}</pre>

<h2>7. Transaction Lifecycle &amp; ACID Properties</h2>
<p>
  In database management, operations modifying multiple tables must execute atomically. If an error occurs halfway through, partial updates create corrupted, desynchronized records. Banlic-CCIRS enforces integrity through <b>ACID Transactions</b>.
</p>

<div class="meta-grid">
  <div class="meta-card">
    <span class="val">ATOMICITY</span>
    <span class="lbl">"All or Nothing" Execution</span>
  </div>
  <div class="meta-card">
    <span class="val">CONSISTENCY</span>
    <span class="lbl">Preserves Schema Invariants</span>
  </div>
  <div class="meta-card">
    <span class="val">ISOLATION</span>
    <span class="lbl">Concurrent User Separation</span>
  </div>
  <div class="meta-card">
    <span class="val">DURABILITY</span>
    <span class="lbl">Persistent Redo Logs</span>
  </div>
</div>

<ul>
  <li><b>Atomicity (All or Nothing):</b> Handled via <code>DB::transaction()</code>. When a resident files a complaint, multiple database operations execute (insert into <code>complaints</code>, store <code>attachments</code>, log initial status in <code>complaint_status_history</code>, write <code>audit_logs</code>). If any step throws an exception, the entire transaction is rolled back (<code>ROLLBACK</code>), preventing partial records.</li>
  <li><b>Consistency:</b> Constraints such as foreign key checks, non-null values, and unique constraints (<code>reference_no</code>) are strictly enforced by MySQL before changes commit.</li>
  <li><b>Isolation:</b> MySQL InnoDB uses <b>Multi-Version Concurrency Control (MVCC)</b> and row-level locks, allowing multiple residents to file complaints simultaneously without race conditions or dirty reads.</li>
  <li><b>Durability:</b> Once committed, updates are written to the InnoDB Write-Ahead Log (WAL / Redo Log) on disk before acknowledging completion, surviving hardware reboots and crashes.</li>
</ul>

<!-- PAGE 8: TRANSACTION CODE IMPLEMENTATION -->
<div class="chapter-break"></div>

<h3>7.1 Production Code Walkthrough: Atomic Filing in <code>ComplaintController.php</code></h3>
<pre><span class="cm">// app/Http/Controllers/ComplaintController.php - Atomic Transaction Implementation</span>
$complaint = DB::transaction(function () use ($data, $request, $user) {
    <span class="cm">// 1. Insert Core Complaint Tuple</span>
    $complaint = Complaint::create([
        'reference_no'   =&gt; $this-&gt;generateReferenceNumber(), <span class="cm">// e.g., BRGY-2026-00014</span>
        'complainant_id' =&gt; $user-&gt;user_id,
        'report_type_id' =&gt; $data['report_type_id'],
        'category_id'    =&gt; $data['category_id'],
        'priority_id'    =&gt; $data['priority_id'],
        'area_id'        =&gt; $data['area_id'],
        'status_id'      =&gt; 1, <span class="cm">// Default initial status: 'Pending'</span>
        'title'          =&gt; $data['title'],
        'description'    =&gt; $data['description'],
        'incident_date'  =&gt; $data['incident_date'],
    ]);

    <span class="cm">// 2. Store Physical Files and Insert Attachment Metadata Tuples</span>
    if ($request-&gt;hasFile('attachments')) {
        foreach ($request-&gt;file('attachments') as $file) {
            $path = $file-&gt;store('complaints', 'public');
            $complaint-&gt;attachments()-&gt;create([
                'uploaded_by' =&gt; $user-&gt;user_id,
                'file_name'   =&gt; $file-&gt;getClientOriginalName(),
                'file_path'   =&gt; $path,
                'file_size'   =&gt; $file-&gt;getSize(),
            ]);
        }
    }

    <span class="cm">// 3. Initialize Status History Audit Timeline</span>
    StatusHistory::create([
        'complaint_id'  =&gt; $complaint-&gt;complaint_id,
        'new_status_id' =&gt; 1,
        'changed_by'    =&gt; $user-&gt;user_id,
        'remarks'       =&gt; 'Complaint filed by resident.',
    ]);

    <span class="cm">// 4. Write Non-Repudiation Security Audit Log</span>
    AuditLog::create([
        'user_id'     =&gt; $user-&gt;user_id,
        'action'      =&gt; 'CREATE',
        'table_name'  =&gt; 'complaints',
        'record_id'   =&gt; $complaint-&gt;complaint_id,
        'description' =&gt; 'Filed complaint ' . $complaint-&gt;reference_no,
        'ip_address'  =&gt; $request-&gt;ip(),
    ]);

    return $complaint; <span class="cm">// All operations committed atomically (COMMIT)</span>
});</pre>

<h3>7.2 Status Transition &amp; Case Resolution Transaction (Admin Controller)</h3>
<p>
  When an authorized officer updates the status or records a formal resolution in <code>Admin\ComplaintController.php</code>, atomic integrity is similarly enforced:
</p>
<pre><span class="cm">// app/Http/Controllers/Admin/ComplaintController.php - Status Update Transaction</span>
DB::transaction(function () use ($complaint, $validated, $oldStatusId, $newStatusId, $statusChanged) {
    if ($statusChanged) {
        $complaint-&gt;status_id = $newStatusId;
        $complaint-&gt;save();

        <span class="cm">// Record immutable historical audit entry</span>
        StatusHistory::create([
            'complaint_id'  =&gt; $complaint-&gt;complaint_id,
            'old_status_id' =&gt; $oldStatusId,
            'new_status_id' =&gt; $newStatusId,
            'changed_by'    =&gt; auth()-&gt;id(),
            'remarks'       =&gt; $validated['remarks'] ?? null,
        ]);
    }

    <span class="cm">// If resolved, enforce 1:1 Resolution record creation</span>
    if ($newStatusId == 5 &amp;&amp; !empty($validated['resolution_details'])) {
        Resolution::updateOrCreate(
            ['complaint_id' =&gt; $complaint-&gt;complaint_id],
            [
                'resolved_by'        =&gt; auth()-&gt;id(),
                'resolution_details' =&gt; $validated['resolution_details'],
                'resolved_at'        =&gt; now(),
            ]
        );
    }
});</pre>

<!-- PAGE 9: SECURITY & PERFORMANCE OPTIMIZATION -->
<div class="chapter-break"></div>

<h2>8. Database Security, Integrity &amp; Privacy (RA 10173)</h2>
<ul>
  <li><b>SQL Injection (SQLi) Prevention:</b> Handled via <b>PDO Parameterized Prepared Statements</b>. Input parameters are sent as separate protocol packets from the SQL execution plan, rendering SQL injection technically impossible.</li>
  <li><b>Cryptographic Credential Security:</b> Passwords are never stored in plaintext. They are hashed using <b>bcrypt</b> (Blowfish cipher) with 12 rounds of salting (<code>BCRYPT_ROUNDS=12</code>), preventing rainbow table and dictionary attacks.</li>
  <li><b>Data Privacy Act Compliance (Resident Masking):</b> On public administrative displays, sensitive resident identities are masked dynamically via the Eloquent Accessor <code>getMaskedComplainantAttribute()</code>:
    <br><code>"Juan Dela Cruz" &rarr; "J*** D*** C***"</code>, protecting victim identities while maintaining case traceability.</li>
</ul>

<h2>9. Physical Performance Tuning &amp; Indexing Strategy</h2>
<h3>9.1 Strategic B+Tree Indexing</h3>
<p>
  MySQL InnoDB creates a <b>Clustered Index</b> on every table's Primary Key. In addition, Banlic-CCIRS provisions targeted <b>Secondary Indexes</b> on frequently filtered foreign keys:
</p>
<ul>
  <li><code>idx_comp_status (status_id)</code>: Accelerates status-filtered dashboard queries (e.g., viewing all "Pending" complaints).</li>
  <li><code>idx_comp_area (area_id)</code>: Optimizes geographic complaint filtering by Purok.</li>
  <li><code>idx_comp_created (created_at)</code>: Powers time-series incident trends and monthly barangay analytics reports without table scans.</li>
  <li><code>idx_assign_to (assigned_to, is_current)</code>: A <b>composite index</b> accelerating staff task resolution by directly filtering active assignments for an officer.</li>
</ul>

<h3>9.2 Eradicating the N+1 Query Problem via Eager Loading</h3>
<div class="callout callout-danger">
  <b>The N+1 Anti-Pattern:</b> If the dashboard fetches 50 complaints and loops through them calling <code>$complaint-&gt;complainant-&gt;full_name</code>, Laravel executes 1 query for the complaints plus 50 individual queries for the users (51 queries total), causing severe database latency.
</div>
<pre><span class="cm">// Optimized Solution in Admin/ComplaintController.php using Eager Loading</span>
$complaints = Complaint::with([
    'complainant',       <span class="cm">// Pre-loads resident records in 1 query: WHERE user_id IN (...)</span>
    'category',          <span class="cm">// Pre-loads incident categories</span>
    'status',            <span class="cm">// Pre-loads status metadata</span>
    'area',              <span class="cm">// Pre-loads purok details</span>
    'currentAssignment.assignee' <span class="cm">// Pre-loads assigned officer</span>
])-&gt;latest()-&gt;paginate(15); <span class="cm">// Reduces 250+ queries to exactly 6 optimized batch queries!</span></pre>

<!-- PAGE 10: DEFENSE QUESTIONS PART 1 -->
<div class="chapter-break"></div>

<h2>10. High-Yield Exam &amp; Capstone Defense Reviewer (Questions 1 to 6)</h2>
<p>
  These questions reflect the most common, challenging technical inquiries asked by university panels and Information Management professors during thesis defense:
</p>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q1</span> Why did your team choose a Relational DBMS (MySQL/MariaDB) over a NoSQL document database like MongoDB?</div>
  <p class="qa-a"><b>Model Answer:</b> Banlic-CCIRS models structured civic processes with strict relational dependencies (complaints, resident verifications, assignments, resolutions). An RDBMS provides ACID transaction guarantees, schema-level referential integrity (foreign keys), and prevents data anomalies through 3NF normalization. NoSQL is suited for unstructured or rapidly changing schemas, whereas barangay complaints require transactional consistency and non-repudiation.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q2</span> Prove that your database schema satisfies Third Normal Form (3NF).</div>
  <p class="qa-a"><b>Model Answer:</b> The schema satisfies 1NF because all attributes are atomic (no multi-valued columns or repeating groups). It satisfies 2NF because non-key attributes are fully functionally dependent on primary keys (verified in <code>area_staff</code> where assignment date depends on the composite key). It satisfies 3NF because all transitive dependencies are eliminated: lookup values (departments, categories, statuses, priorities) exist in independent reference tables rather than being duplicated inside the complaints table.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q3</span> What is the role of an ORM like Eloquent, and does it replace SQL?</div>
  <p class="qa-a"><b>Model Answer:</b> Eloquent acts as an Object-Relational Mapping bridge between PHP's object-oriented domain and MySQL's relational tables using the Active Record pattern. It abstracts manual SQL string composition, manages relationships, and provides mass-assignment protection. It does not replace SQL; instead, it compiles model operations into optimized, parameterized SQL queries executed via PDO.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q4</span> What happens to child records when a complaint is deleted? Explain your ON DELETE rules.</div>
  <p class="qa-a"><b>Model Answer:</b> Referential integrity rules govern parent-child relationships. If a complaint is deleted, foreign keys in <code>attachments</code>, <code>complaint_status_history</code>, <code>investigations</code>, and <code>resolutions</code> execute <code>ON DELETE CASCADE</code>, safely purging dependent data and preventing orphaned records. Conversely, foreign keys referencing users or departments utilize <code>ON DELETE SET NULL</code> or <code>RESTRICT</code> to protect historical accountability.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q5</span> How does your system protect against SQL Injection attacks?</div>
  <p class="qa-a"><b>Model Answer:</b> SQL Injection occurs when untrusted user input is concatenated directly into SQL command strings. Banlic-CCIRS prevents this by using Eloquent and Laravel's Query Builder, which rely exclusively on <b>PDO Prepared Statements</b>. Input parameters are transmitted separately from the SQL statement template, ensuring input is treated as raw data rather than executable code.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q6</span> Why do you use reference_no in URLs (/complaints/BRGY-2026-00012) instead of complaint_id?</div>
  <p class="qa-a"><b>Model Answer:</b> Exposing sequential internal auto-increment primary keys creates an <b>Insecure Direct Object Reference (IDOR)</b> vulnerability, allowing attackers to enumerate records. Using an unpredictable reference number implemented via Eloquent's <code>getRouteKeyName()</code> conceals internal database primary keys and provides a human-readable tracking code for residents.</p>
</div>

<!-- PAGE 11: DEFENSE QUESTIONS PART 2 -->
<div class="chapter-break"></div>

<h2>10. High-Yield Exam &amp; Capstone Defense Reviewer (Questions 7 to 12)</h2>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q7</span> How are database migrations superior to importing a raw .sql file?</div>
  <p class="qa-a"><b>Model Answer:</b> Migrations provide version control for database schemas. They allow developers to track incremental schema modifications over time, roll back erroneous changes via the <code>down()</code> method, and ensure consistency across development, staging, and production environments without manual, error-prone SQL dump imports.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q8</span> Where are database transactions implemented in your codebase, and what problem do they solve?</div>
  <p class="qa-a"><b>Model Answer:</b> In <code>ComplaintController@store</code> and <code>Admin\ComplaintController@updateStatus</code>, updates are enclosed within <code>DB::transaction()</code>. When filing a complaint, if inserting file attachments fails after the main complaint record is created, the transaction issues an automatic <code>ROLLBACK</code>, preventing corrupted, partial records from persisting in the database.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q9</span> How does your system ensure data confidentiality and resident privacy under Republic Act 10173?</div>
  <p class="qa-a"><b>Model Answer:</b> The system applies privacy-by-design. Passwords are encrypted with salted bcrypt (cost 12), preventing exposure even during a database breach. Resident identities on public dispatch lists are dynamically masked (<code>getMaskedComplainantAttribute()</code>), and verification ID files are stored on isolated filesystem paths restricted by role middleware.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q10</span> How does database indexing work under the hood in MySQL InnoDB?</div>
  <p class="qa-a"><b>Model Answer:</b> InnoDB organizes table records in a <b>B+Tree Clustered Index</b> ordered by Primary Key. Secondary indexes (e.g., <code>status_id</code>, <code>area_id</code>) store the indexed column value alongside a pointer to the primary key. When filtering, MySQL traverses the B+Tree in O(log N) time to retrieve primary keys, bypassing slow, exhaustive full-table scans.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q11</span> What is the significance of database transactions during complaint status reassignment?</div>
  <p class="qa-a"><b>Model Answer:</b> Reassigning a case requires modifying three tables: deactivating the previous assignment (<code>is_current = false</code>), inserting the new assignment, and recording a status history event. Wrapping these in <code>DB::transaction()</code> ensures all three operations succeed together; a network failure cannot leave two active officers or missing timeline logs.</p>
</div>

<div class="qa-card">
  <div class="qa-q"><span class="q-tag">Q12</span> What are audit logs, and why are they critical for accountability in government information systems?</div>
  <p class="qa-a"><b>Model Answer:</b> The <code>audit_logs</code> table maintains an immutable non-repudiation record of all administrative actions (CREATE, UPDATE, ASSIGN, STATUS_CHANGE). It captures the actor's user ID, IP address, target table, record ID, and timestamp, guaranteeing accountability, dispute traceability, and audit compliance for barangay governance.</p>
</div>

<div class="callout callout-info" style="margin-top: 10px;">
  <b>Summary for Capstone Defense:</b> When presenting your database architecture, emphasize that the database was engineered with <b>mathematical rigor (3NF)</b>, <b>bulletproof integrity (foreign key cascades &amp; ACID transactions)</b>, and <b>enterprise integration (Laravel Eloquent ORM &amp; PDO prepared statements)</b>.
</div>

<div class="footer-bar">
  <span>Barangay Banlic CCIRS &bull; Information Management Database Reviewer</span>
  <span>Academic Year 2026 &bull; Technical Compendium</span>
</div>

</body>
</html>
"""
    return html.replace("__LOGO_B64__", logo_b64)

def main():
    print("Building polished Information Management Database Reviewer...")
    logo_b64 = get_logo_base64()
    html_content = build_html(logo_b64)
    
    os.makedirs("docs", exist_ok=True)
    html_file = "docs/Information_Management_Database_Reviewer_Banlic_CCIRS.html"
    pdf_file = "docs/Information_Management_Database_Reviewer_Banlic_CCIRS.pdf"
    
    with open(html_file, "w", encoding="utf-8") as f:
        f.write(html_content)
    print(f"HTML saved to: {html_file}")
    
    chrome_bin = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
    cmd = [
        chrome_bin,
        "--headless",
        "--disable-gpu",
        "--no-pdf-header-footer",
        f"--print-to-pdf={pdf_file}",
        html_file
    ]
    res = subprocess.run(cmd, capture_output=True, text=True)
    
    if os.path.exists(pdf_file):
        size = os.path.getsize(pdf_file)
        print(f"PDF generated: {pdf_file} ({size} bytes)")
        p_res = subprocess.run(["mdls", "-name", "kMDItemNumberOfPages", pdf_file], capture_output=True, text=True)
        print("Page count check:", p_res.stdout.strip())
    else:
        print("Error compiling PDF:", res.stderr)
        sys.exit(1)

if __name__ == "__main__":
    main()
