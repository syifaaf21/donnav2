<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubKlausulsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tm_sub_klausuls')->insert([
            // Klausul IATF 16949:2016
            ['head_klausul_id' => '1', 'code' => '4.1', 'name' => 'Understanding the organization and its context'],
            ['head_klausul_id' => '1', 'code' => '4.2', 'name' => 'Understanding the needs and expectations of interested parties'],
            ['head_klausul_id' => '1', 'code' => '4.3', 'name' => 'Determining the scope of the quality management system'],
            ['head_klausul_id' => '1', 'code' => '4.3.1', 'name' => 'Automotive-specific: customer-specific requirements & product-safety relevance'],
            ['head_klausul_id' => '1', 'code' => '4.4', 'name' => 'Quality management system and its processes'],

            ['head_klausul_id' => '2', 'code' => '5.1', 'name' => 'Leadership and commitment'],
            ['head_klausul_id' => '2', 'code' => '5.2', 'name' => 'Quality policy'],
            ['head_klausul_id' => '2', 'code' => '5.3', 'name' => 'Organizational roles, responsibilities and authorities'],

            ['head_klausul_id' => '3', 'code' => '6.1', 'name' => 'Actions to address risks and opportunities'],
            ['head_klausul_id' => '3', 'code' => '6.1.1', 'name' => 'Risk analysis records'],
            ['head_klausul_id' => '3', 'code' => '6.1.2', 'name' => 'Automotive-specific: contingency planning & preventive actions'],
            ['head_klausul_id' => '3', 'code' => '6.2', 'name' => 'Quality objectives and planning to achieve them'],
            ['head_klausul_id' => '3', 'code' => '6.3', 'name' => 'Planning of changes'],

            ['head_klausul_id' => '4', 'code' => '7.1', 'name' => 'Resources'],
            ['head_klausul_id' => '4', 'code' => '7.1.1', 'name' => 'General resources'],
            ['head_klausul_id' => '4', 'code' => '7.1.2', 'name' => 'People'],
            ['head_klausul_id' => '4', 'code' => '7.1.3', 'name' => 'Infrastructure'],
            ['head_klausul_id' => '4', 'code' => '7.1.4', 'name' => 'Environment for operation'],
            ['head_klausul_id' => '4', 'code' => '7.1.5', 'name' => 'Monitoring and measuring resources'],
            ['head_klausul_id' => '4', 'code' => '7.1.5.1', 'name' => 'Automotive-specific: customer acceptance for alternate methods'],
            ['head_klausul_id' => '4', 'code' => '7.1.5.2', 'name' => 'Calibration & verification process and records'],
            ['head_klausul_id' => '4', 'code' => '7.1.6', 'name' => 'Organizational knowledge'],
            ['head_klausul_id' => '4', 'code' => '7.2', 'name' => 'Competence'],
            ['head_klausul_id' => '4', 'code' => '7.2.1', 'name' => 'Determine and maintain competence'],
            ['head_klausul_id' => '4', 'code' => '7.2.3', 'name' => 'Internal auditor competency—training, records, list of qualified auditors'],
            ['head_klausul_id' => '4', 'code' => '7.2.4', 'name' => 'Second-party auditor competency'],
            ['head_klausul_id' => '4', 'code' => '7.3', 'name' => 'Awareness'],
            ['head_klausul_id' => '4', 'code' => '7.3.1', 'name' => 'Employee awareness of quality objectives'],
            ['head_klausul_id' => '4', 'code' => '7.3.2', 'name' => 'Automotive-specific: documented process to motivate staff'],
            ['head_klausul_id' => '4', 'code' => '7.4', 'name' => 'Communication'],
            ['head_klausul_id' => '4', 'code' => '7.5', 'name' => 'Documented information'],
            ['head_klausul_id' => '4', 'code' => '7.5.1', 'name' => 'General (process charts, customer-specific mapping)'],
            ['head_klausul_id' => '4', 'code' => '7.5.2', 'name' => 'Creating and updating documentation'],
            ['head_klausul_id' => '4', 'code' => '7.5.3', 'name' => 'Control of documented information (e.g., quality manual, record retention)'],

            ['head_klausul_id' => '5', 'code' => '8.1', 'name' => 'Operational planning and control'],
            ['head_klausul_id' => '5', 'code' => '8.2', 'name' => 'Requirements for products and services'],
            ['head_klausul_id' => '5', 'code' => '8.2.1', 'name' => 'Customer communication'],
            ['head_klausul_id' => '5', 'code' => '8.2.2', 'name' => 'Determining requirements'],
            ['head_klausul_id' => '5', 'code' => '8.2.3', 'name' => 'Reviewing requirements'],
            ['head_klausul_id' => '5', 'code' => '8.2.4', 'name' => 'Changes to requirements'],
            ['head_klausul_id' => '5', 'code' => '8.3', 'name' => 'Design and development of products and services'],
            ['head_klausul_id' => '5', 'code' => '8.3.1', 'name' => 'General'],
            ['head_klausul_id' => '5', 'code' => '8.3.2', 'name' => 'Design and development planning'],
            ['head_klausul_id' => '5', 'code' => '8.3.2.1', 'name' => 'Automotive: involve stakeholders & supply chain in planning'],
            ['head_klausul_id' => '5', 'code' => '8.3.3', 'name' => 'Design input (product + manufacturing process, special characteristics)'],
            ['head_klausul_id' => '5', 'code' => '8.3.4', 'name' => 'Design output'],
            ['head_klausul_id' => '5', 'code' => '8.3.5', 'name' => 'Design review, verification & validation'],
            ['head_klausul_id' => '5', 'code' => '8.3.6', 'name' => 'Design changes'],
            ['head_klausul_id' => '5', 'code' => '8.4', 'name' => 'Control of externally provided processes, products and services'],
            ['head_klausul_id' => '5', 'code' => '8.4.1', 'name' => 'Supplier evaluation and selection'],
            ['head_klausul_id' => '5', 'code' => '8.4.2', 'name' => 'Outsourced process control'],
            ['head_klausul_id' => '5', 'code' => '8.5', 'name' => 'Production and service provision'],
            ['head_klausul_id' => '5', 'code' => '8.5.1', 'name' => 'Control of production (process controls, SPC, job set-up, TPM)'],
            ['head_klausul_id' => '5', 'code' => '8.5.2', 'name' => 'Identification & traceability'],
            ['head_klausul_id' => '5', 'code' => '8.5.3', 'name' => 'Customer property'],
            ['head_klausul_id' => '5', 'code' => '8.5.4', 'name' => 'Preservation'],
            ['head_klausul_id' => '5', 'code' => '8.5.5', 'name' => 'Post-delivery activities'],
            ['head_klausul_id' => '5', 'code' => '8.5.6', 'name' => 'Control of changes'],
            ['head_klausul_id' => '5', 'code' => '8.5.6.1', 'name' => 'Automotive: alternative methods & list of process controls'],
            ['head_klausul_id' => '5', 'code' => '8.6', 'name' => 'Release of products and services'],
            ['head_klausul_id' => '5', 'code' => '8.7', 'name' => 'Control of nonconforming outputs'],
            ['head_klausul_id' => '5', 'code' => '8.7.1', 'name' => 'Nonconforming product handling (concession, rework, repair, notify customer)'],
            ['head_klausul_id' => '5', 'code' => '8.7.2', 'name' => 'Records'],

            ['head_klausul_id' => '6', 'code' => '9.1', 'name' => 'Monitoring, measurement, analysis and evaluation'],
            ['head_klausul_id' => '6', 'code' => '9.1.1', 'name' => 'Production & process monitoring'],
            ['head_klausul_id' => '6', 'code' => '9.1.2', 'name' => 'Automotive: measurement system analysis (MSA), SPC'],
            ['head_klausul_id' => '6', 'code' => '9.2', 'name' => 'Internal audit'],
            ['head_klausul_id' => '6', 'code' => '9.2.2', 'name' => 'Audit program & auditor competence'],
            ['head_klausul_id' => '6', 'code' => '9.3', 'name' => 'Management review'],
            ['head_klausul_id' => '6', 'code' => '9.3.3', 'name' => 'Review outputs & follow-up actions'],

            ['head_klausul_id' => '7', 'code' => '10.1', 'name' => 'General'],
            ['head_klausul_id' => '7', 'code' => '10.2', 'name' => 'Nonconformity and corrective action (including error-proofing, 8D, 5 Why, etc.)'],
            ['head_klausul_id' => '7', 'code' => '10.3', 'name' => 'Continual improvement'],
            ['head_klausul_id' => '7', 'code' => '10.3.1', 'name' => 'Automotive: promote quality & tech awareness'],

            // Klausul ISO 14001:2015
            ['head_klausul_id' => '8', 'code' => '4.1', 'name' => 'Memahami organisasi dan konteksnya'],
            ['head_klausul_id' => '8', 'code' => '4.2', 'name' => 'Memahami kebutuhan dan harapan pihak berkepentingan'],
            ['head_klausul_id' => '8', 'code' => '4.3', 'name' => 'Menentukan ruang lingkup sistem manajemen lingkungan'],
            ['head_klausul_id' => '8', 'code' => '4.4', 'name' => 'Sistem manajemen lingkungan'],

            ['head_klausul_id' => '9', 'code' => '5.1', 'name' => 'Kepemimpinan dan komitmen'],
            ['head_klausul_id' => '9', 'code' => '5.2', 'name' => 'Kebijakan lingkungan'],
            ['head_klausul_id' => '9', 'code' => '5.3', 'name' => 'Peran, tanggung jawab, dan wewenang dalam organisasi'],

            ['head_klausul_id' => '10', 'code' => '6.1', 'name' => 'Tindakan untuk mengatasi risiko dan peluang'],
            ['head_klausul_id' => '10', 'code' => '6.1.2', 'name' => 'Aspek lingkungan'],
            ['head_klausul_id' => '10', 'code' => '6.1.3', 'name' => 'Kewajiban kepatuhan'],
            ['head_klausul_id' => '10', 'code' => '6.2', 'name' => 'Sasaran lingkungan dan perencanaan untuk mencapainya'],

            ['head_klausul_id' => '11', 'code' => '7.1', 'name' => 'Sumber daya'],
            ['head_klausul_id' => '11', 'code' => '7.2', 'name' => 'Kompetensi'],
            ['head_klausul_id' => '11', 'code' => '7.3', 'name' => 'Kesadaran'],
            ['head_klausul_id' => '11', 'code' => '7.4', 'name' => 'Komunikasi'],
            ['head_klausul_id' => '11', 'code' => '7.5', 'name' => 'Informasi terdokumentasi'],

            ['head_klausul_id' => '12', 'code' => '8.1', 'name' => 'Perencanaan dan pengendalian operasional'],
            ['head_klausul_id' => '12', 'code' => '8.2', 'name' => 'Kesiapsiagaan dan tanggap darurat'],

            ['head_klausul_id' => '13', 'code' => '9.1', 'name' => 'Pemantauan, pengukuran, analisis, dan evaluasi'],
            ['head_klausul_id' => '13', 'code' => '9.2', 'name' => 'Audit internal'],
            ['head_klausul_id' => '13', 'code' => '9.3', 'name' => 'Tinjauan manajemen'],

            ['head_klausul_id' => '14', 'code' => '10.1', 'name' => 'Ketidaksesuaian dan tindakan korektif'],
            ['head_klausul_id' => '14', 'code' => '10.2', 'name' => 'Peningkatan berkelanjutan'],

            // Klausul ISO 45001:2018
            ['head_klausul_id' => '15', 'code' => '4.1', 'name' => 'Memahami organisasi dan konteksnya'],
            ['head_klausul_id' => '15', 'code' => '4.2', 'name' => 'Memahami kebutuhan dan harapan pihak berkepentingan'],
            ['head_klausul_id' => '15', 'code' => '4.3', 'name' => 'Menentukan ruang lingkup SMK3'],
            ['head_klausul_id' => '15', 'code' => '4.4', 'name' => 'Sistem manajemen K3'],

            ['head_klausul_id' => '16', 'code' => '5.1', 'name' => 'Kepemimpinan dan komitmen'],
            ['head_klausul_id' => '16', 'code' => '5.2', 'name' => 'Kebijakan K3'],
            ['head_klausul_id' => '16', 'code' => '5.3', 'name' => 'Peran, tanggung jawab, dan wewenang'],
            ['head_klausul_id' => '16', 'code' => '5.4', 'name' => 'Konsultasi dan partisipasi pekerja'],

            ['head_klausul_id' => '17', 'code' => '6.1', 'name' => 'Tindakan untuk mengatasi risiko dan peluang'],
            ['head_klausul_id' => '17', 'code' => '6.1.2', 'name' => 'Identifikasi bahaya dan penilaian risiko K3'],
            ['head_klausul_id' => '17', 'code' => '6.1.3', 'name' => 'Kewajiban peraturan'],
            ['head_klausul_id' => '17', 'code' => '6.2', 'name' => 'Sasaran K3 dan perencanaan untuk mencapainya'],

            ['head_klausul_id' => '18', 'code' => '7.1', 'name' => 'Sumber daya'],
            ['head_klausul_id' => '18', 'code' => '7.2', 'name' => 'Kompetensi'],
            ['head_klausul_id' => '18', 'code' => '7.3', 'name' => 'Kesadaran'],
            ['head_klausul_id' => '18', 'code' => '7.4', 'name' => 'Komunikasi'],
            ['head_klausul_id' => '18', 'code' => '7.5', 'name' => 'Informasi terdokumentasi'],

            ['head_klausul_id' => '19', 'code' => '8.1', 'name' => 'Perencanaan dan pengendalian operasional'],
            ['head_klausul_id' => '19', 'code' => '8.2', 'name' => 'Kesiapsiagaan dan tanggap darurat'],

            ['head_klausul_id' => '20', 'code' => '9.1', 'name' => 'Pemantauan, pengukuran, analisis, dan evaluasi'],
            ['head_klausul_id' => '20', 'code' => '9.2', 'name' => 'Audit internal'],
            ['head_klausul_id' => '20', 'code' => '9.3', 'name' => 'Tinjauan manajemen'],

            ['head_klausul_id' => '21', 'code' => '10.1', 'name' => 'Ketidaksesuaian dan tindakan korektif'],
            ['head_klausul_id' => '21', 'code' => '10.2', 'name' => 'Peningkatan berkelanjutan'],
        ]);
    }
}
