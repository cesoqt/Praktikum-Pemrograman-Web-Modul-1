USE praktikum_web_2401020031;

INSERT INTO program_studi (nama_prodi) VALUES
    ('Teknik Elektro'),
    ('Teknik Perkapalan');

INSERT INTO mahasiswa
    (nim, nama, email, usia, program_studi_id) VALUES
    ('2301030001', 'Rina Marlina',     'rina@example.com',      20, 1),
    ('2301030002', 'Dimas Prakoso',    'dimas@example.com',     21, 1),
    ('2301040001', 'Fajar Nugraha',    'fajar@example.com',     19, 2),
    ('2301040099', 'Data Sementara',   'sementara@example.com', 18, 2);

UPDATE mahasiswa
SET email = 'rina.marlina@example.com'
WHERE nim = '2301030001';

DELETE FROM mahasiswa WHERE nim = '2301040099';

SELECT m.nim, m.nama, m.email, m.usia,
       p.nama_prodi
FROM mahasiswa AS m
JOIN program_studi AS p
    ON p.id = m.program_studi_id
ORDER BY m.nim;
