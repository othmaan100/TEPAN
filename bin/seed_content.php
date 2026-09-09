<?php
/**
 * Seed the editable policy / guideline pages with COPE-aligned starter text.
 * Safe to re-run: existing slugs are left untouched.
 *
 *   C:\xampp\php\php.exe bin\seed_content.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

require_once __DIR__ . '/../config/database.php';

$pages = [
    [
        'slug' => 'aims-and-scope',
        'title' => 'Aims & Scope',
        'nav_group' => 'policies',
        'sort_order' => 1,
        'body' => <<<HTML
<p>The TEPAN journal publishes original, peer-reviewed research that advances the theory and practice of
technology education in Nigeria and comparable contexts. It serves lecturers, teachers, researchers,
curriculum developers, and policymakers.</p>
<h3>Scope</h3>
<ul>
  <li>Curriculum design, pedagogy and assessment in technical, vocational and technology education</li>
  <li>Teacher preparation and continuing professional development</li>
  <li>Workshop practice, laboratory safety and instructional resources</li>
  <li>Digital tools, e-learning and emerging technologies in the classroom</li>
  <li>Education policy, access, equity and industry linkages</li>
</ul>
<p>Submissions must be original, not previously published, and not under consideration elsewhere.</p>
HTML,
    ],
    [
        'slug' => 'author-guidelines',
        'title' => 'Author Guidelines',
        'nav_group' => 'authors',
        'sort_order' => 1,
        'body' => <<<HTML
<p>Please read these guidelines fully before submitting. Manuscripts that do not follow them may be returned
without review.</p>
<h3>Manuscript preparation</h3>
<ul>
  <li><strong>Format:</strong> Microsoft Word (.doc/.docx) or PDF, A4, double-spaced, 12&nbsp;pt Times New Roman.</li>
  <li><strong>Length:</strong> 3,000&ndash;6,000 words including references.</li>
  <li><strong>Structure:</strong> Title; Abstract (≤250 words); 3&ndash;5 keywords; Introduction; Methodology;
      Results; Discussion; Conclusion; References.</li>
  <li><strong>Referencing:</strong> APA 7th edition, cited consistently in text and in the reference list.</li>
  <li><strong>Tables and figures:</strong> numbered, captioned, and referenced in the text.</li>
</ul>
<h3>Anonymity for review</h3>
<p>The journal operates double-anonymous peer review. Upload a fully anonymised manuscript: remove author
names, affiliations, acknowledgements and self-identifying references from the file and its document
properties. Enter author details only in the submission form.</p>
<h3>What to submit</h3>
<ul>
  <li>Anonymised manuscript file</li>
  <li>Abstract and keywords (in the form)</li>
  <li>A short cover letter stating the work is original and not under review elsewhere</li>
  <li>Optional supplementary material (data, instruments, appendices)</li>
</ul>
<h3>Templates</h3>
<p>Download the manuscript template and the copyright/author agreement form from the
<a href="/TEPA/author-resources.php">Author Resources</a> page. The signed copyright form is required
before an accepted article is published.</p>
<h3>Submission</h3>
<p>Submit online through the <a href="/TEPA/submit.php">manuscript submission</a> page. You will receive a
reference number and can check progress on the <a href="/TEPA/submission-status.php">submission status</a>
page.</p>
HTML,
    ],
    [
        'slug' => 'publication-ethics',
        'title' => 'Publication Ethics & Malpractice Statement',
        'nav_group' => 'policies',
        'sort_order' => 2,
        'body' => <<<HTML
<p>This statement follows the principles of the Committee on Publication Ethics (COPE). All parties &mdash;
authors, editors, reviewers and the publisher &mdash; are expected to uphold them.</p>
<h3>Authors</h3>
<ul>
  <li>Report work that is original, accurate and free of fabrication, falsification or inappropriate data
      manipulation.</li>
  <li>Ensure the manuscript has not been published elsewhere and is not simultaneously under review.</li>
  <li>Credit all and only those who made a substantial contribution as authors; disclose all sources of
      funding and any conflicts of interest.</li>
  <li>Cite all sources properly and obtain permission for reused material.</li>
  <li>Promptly notify the editor of any significant error found after publication and cooperate with a
      correction or retraction.</li>
</ul>
<h3>Editors</h3>
<ul>
  <li>Decide which submissions are published based only on academic merit, relevance and originality,
      without regard to race, gender, religion, nationality or institutional affiliation of the authors.</li>
  <li>Handle submissions confidentially and manage conflicts of interest by recusal where necessary.</li>
  <li>Investigate credible allegations of misconduct, before or after publication, and issue corrections,
      expressions of concern or retractions as warranted.</li>
</ul>
<h3>Reviewers</h3>
<ul>
  <li>Treat manuscripts as confidential and do not use unpublished material for personal advantage.</li>
  <li>Provide objective, constructive and timely assessments and declare any conflict of interest.</li>
  <li>Alert the editor to substantial similarity with other work or to suspected ethical breaches.</li>
</ul>
<h3>Handling misconduct</h3>
<p>Allegations of plagiarism, data fabrication, redundant publication, authorship disputes or undisclosed
conflicts are investigated in line with the relevant COPE flowcharts. Outcomes may include rejection,
retraction, notification of the authors' institution, and a publication ban.</p>
HTML,
    ],
    [
        'slug' => 'peer-review-policy',
        'title' => 'Peer Review Policy',
        'nav_group' => 'policies',
        'sort_order' => 3,
        'body' => <<<HTML
<p>Every research article is subject to double-anonymous peer review: reviewers do not know the identity of
the authors and authors do not know the identity of the reviewers.</p>
<h3>Process</h3>
<ol>
  <li><strong>Initial check.</strong> The editor screens each submission for scope, completeness, adherence
      to the guidelines and originality (including a similarity check). Out-of-scope or non-compliant
      manuscripts are returned or rejected without external review.</li>
  <li><strong>Assignment.</strong> Suitable manuscripts are sent to at least two independent reviewers with
      relevant expertise and no conflict of interest.</li>
  <li><strong>Review.</strong> Reviewers assess originality, methodology, evidence, clarity and contribution,
      and recommend one of: accept, minor revisions, major revisions, or reject.</li>
  <li><strong>Decision.</strong> The editor makes the decision based on the reviews and communicates it to
      the corresponding author with the anonymised reviewer comments.</li>
  <li><strong>Revision.</strong> Authors return revised manuscripts with a point-by-point response.
      Major revisions are usually sent back to the original reviewers.</li>
</ol>
<h3>Timeline</h3>
<p>We aim to return a first decision within 8&ndash;10 weeks. Reviewers are normally given 3&nbsp;weeks.
These are targets, not guarantees.</p>
<h3>Appeals</h3>
<p>Authors may appeal a decision once, in writing to the editor, setting out specific grounds. The editor's
decision on an appeal is final.</p>
HTML,
    ],
    [
        'slug' => 'open-access-licensing',
        'title' => 'Open Access & Licensing',
        'nav_group' => 'policies',
        'sort_order' => 4,
        'body' => <<<HTML
<p>The journal is fully open access. All articles are freely available to read, download and share
immediately on publication, with no subscription or reader fees.</p>
<h3>Licence</h3>
<p>Articles are published under the <strong>Creative Commons Attribution 4.0 International (CC&nbsp;BY&nbsp;4.0)</strong>
licence. Anyone may copy, redistribute, adapt and build upon the work for any purpose, including
commercially, provided appropriate credit is given to the authors and the journal, a link to the licence is
provided, and any changes are indicated.</p>
<p>Full licence text: <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener">https://creativecommons.org/licenses/by/4.0/</a></p>
<h3>Copyright</h3>
<p>Authors retain the copyright to their work and grant the journal the right of first publication.
See the <a href="/TEPA/page.php?slug=copyright-agreement">Copyright / Author Agreement</a> for details.</p>
<h3>Charges</h3>
<p>State the journal's position on article processing or submission charges here (for example, "There are no
submission or article processing charges").</p>
HTML,
    ],
    [
        'slug' => 'copyright-agreement',
        'title' => 'Copyright / Author Agreement',
        'nav_group' => 'authors',
        'sort_order' => 2,
        'body' => <<<HTML
<p>On acceptance, the corresponding author must sign and return the Copyright / Author Agreement form on
behalf of all authors. Download it from <a href="/TEPA/author-resources.php">Author Resources</a>.</p>
<h3>Key terms</h3>
<ul>
  <li>The authors confirm the work is original, is their own, and does not infringe any third-party rights.</li>
  <li>The authors retain copyright and moral rights in the article.</li>
  <li>The authors grant TEPAN the right of first publication and a perpetual, non-exclusive licence to
      publish, archive and distribute the article.</li>
  <li>The article is released to readers under the CC&nbsp;BY&nbsp;4.0 licence.</li>
  <li>The corresponding author warrants that all named authors have approved the submission and the
      agreement, and that any required institutional or ethical approvals were obtained.</li>
</ul>
<p>Publication of an accepted article will not proceed until the signed form has been received.</p>
HTML,
    ],
    [
        'slug' => 'plagiarism-policy',
        'title' => 'Plagiarism Policy',
        'nav_group' => 'authors',
        'sort_order' => 3,
        'body' => <<<HTML
<p>The journal has zero tolerance for plagiarism. Every submission is checked with similarity-detection
software during the initial editorial screening and again before publication.</p>
<h3>What counts as plagiarism</h3>
<ul>
  <li>Presenting another person's ideas, words, data or figures as one's own</li>
  <li>Copying text without quotation marks and a citation</li>
  <li>Paraphrasing sources too closely without attribution</li>
  <li>Re-using one's own previously published work without disclosure (self-plagiarism / redundant publication)</li>
</ul>
<h3>Action</h3>
<ul>
  <li><strong>Before acceptance:</strong> manuscripts with significant unattributed overlap are rejected.
      Minor issues are returned to the author for correction.</li>
  <li><strong>After publication:</strong> confirmed plagiarism leads to a correction or retraction, a note
      to the authors' institution, and may result in a ban on future submissions.</li>
</ul>
<p>Authors should quote sparingly, always attribute, and cite their own earlier work where relevant.</p>
HTML,
    ],
    [
        'slug' => 'archiving-policy',
        'title' => 'Archiving & Preservation Policy',
        'nav_group' => 'policies',
        'sort_order' => 5,
        'body' => <<<HTML
<p>The journal is committed to the long-term availability of its content.</p>
<ul>
  <li><strong>Primary hosting:</strong> all issues and articles are hosted on this website and remain freely
      accessible after publication.</li>
  <li><strong>Local preservation:</strong> the full text and metadata of every published item are backed up
      regularly to separate storage held by TEPAN.</li>
  <li><strong>Distributed preservation:</strong> state here any external archiving arrangement the journal
      participates in (for example the PKP Preservation Network / LOCKSS, or a national library deposit).</li>
  <li><strong>Author self-archiving:</strong> authors may deposit the published version of their article in
      an institutional or subject repository and on their personal or departmental website, with a link to
      the version of record and the CC&nbsp;BY licence noted.</li>
  <li><strong>Persistent identifiers:</strong> state here whether articles are assigned DOIs and, if so,
      through which registration agency.</li>
</ul>
HTML,
    ],
];

$stmt = db()->prepare("SELECT COUNT(*) FROM pages WHERE slug = ?");
$insert = db()->prepare(
    "INSERT INTO pages (slug, title, body, nav_group, sort_order, is_published) VALUES (?, ?, ?, ?, ?, 1)"
);

$added = 0;
foreach ($pages as $p) {
    $stmt->execute([$p['slug']]);
    if ((int)$stmt->fetchColumn() > 0) {
        echo "skip  {$p['slug']} (already exists)\n";
        continue;
    }
    $insert->execute([$p['slug'], $p['title'], $p['body'], $p['nav_group'], $p['sort_order']]);
    echo "add   {$p['slug']}\n";
    $added++;
}

echo "\nDone. $added page(s) added.\n";
