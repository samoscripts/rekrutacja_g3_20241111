<?php
namespace service;

use Exception;
use model\DatabaseFactory;
use model\DatabaseInterface;

class Form
{


    private DatabaseInterface $db;
    private array $errors = [];

    /**
     * @throws Exception
     */
    public function __construct($dbType)
    {
        $this->db = DatabaseFactory::create($dbType);
        $this->db->connect();
        if(!$this->db->tableExists('zadanie')) {
            $this->db->restoreDb();
        }
        try {
            $this->handleRequest();
        } catch (Exception $e) {
            if(empty($this->errors)) {
                $this->errors[] = 'Błąd!';
            }
            echo json_encode(['errors' => $this->errors, 'message' => $e->getMessage()]);
        }
    }

    private function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            switch ($_POST['method']) {
                case 'save_form':
                    $this->saveForm($_POST);
                    echo json_encode(['success' => true, 'message' => 'Form saved']);
                    die;
                case 'get_all_data':
                    $data = $this->getAllDataFromDb();
                    echo json_encode([
                        'tableData' => $data,
                        'surnameCounter' => $this->getSurnameCounter('Samotyj'),
                        'emailDomainCounter' => $this->getMailDomainCounter('gmail.com')]);
                    die;
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            switch ($_GET['method']) {
                case 'get_all_data':
                    $data = $this->getAllDataFromDb();
                    echo json_encode([
                        'tableData' => $data,
                        'surnameCounter' => $this->getSurnameCounter($_GET['surname']),
                        'emailDomainCounter' => $this->getMailDomainCounter($_GET['emailDomain'])
                    ]);
                    die;
                case 'restore_db':
                    $this->db->restoreDb();
                    echo json_encode(['success' => true, 'message' => 'Database restored']);
                    die;
            }
        }
    }

    private function saveForm($post): void
    {
        $data = $this->sanitizeData($post);
        $this->validateData($post);
        $formData = [
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'choose' => $data['choose'],
            'client_no' => $data['client_no'],
            'agreement1' => isset($data['agreement1']) ? 1 : 0,
            'agreement2' => isset($data['agreement2']) ? 1 : 0,
            'agreement3' => isset($data['agreement3']) ? 1 : 0,
            'account' => isset($data['account']) ? $data['account'] : '',
            'user_info' => isset($data['user_info']) ? $data['user_info'] : '',
        ];
        $this->db->executeQuery($this->getInsertQuery(), $formData);
    }

    private function getInsertQuery(): string
    {
        return <<<SQL
INSERT INTO zadanie (
    name,
    surname,
    email,
    phone,
    choose,
    client_no,
    agreement1,
    agreement2,
    agreement3,
    account,
    user_info
) VALUES (
    :name,
    :surname,
    :email,
    :phone,
    :choose,
    :client_no,
    :agreement1,
    :agreement2,
    :agreement3,
    :account,
    :user_info
)
SQL;
    }

    private function getAllDataFromDb(): array
    {
        return $this->db->fetchAll("SELECT * FROM zadanie");
    }

    private function getSurnameCounter($surname): int
    {
        return $this->db->fetchColumn("SELECT COUNT(id_form) FROM zadanie WHERE surname = :surname", [':surname' => $surname]);
    }

    private function getMailDomainCounter($mailDomain): int
    {
        return $this->db->fetchColumn("SELECT COUNT(id_form) FROM zadanie WHERE email LIKE ?", ['%@' . $mailDomain]);
    }

    private function sanitizeData($data): array
    {
        $sanitized_data = [];
        foreach ($data as $key => $value) {
            $sanitized_data[$key] = htmlspecialchars(strip_tags(trim($value)));
        }
        return $sanitized_data;
    }

    private function validateData($post): void
    {
        if (empty($post['name'])) {
            $this->errors[] = 'Name is required';
        } elseif (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+$/', $post['name'])) {
            $this->errors[] = 'Name must contain only letters';
        }

        if (empty($post['surname'])) {
            $this->errors[] = 'Surname is required';
        } elseif (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ \'-]+$/', $post['surname'])) {
            $this->errors[] = 'Surname must contain only letters, spaces, hyphens, or apostrophes';
        }

        if (empty($post['email'])) {
            $this->errors[] = 'Email is required';
        } elseif (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid email format';
        }

        if (!empty($post['phone']) && !preg_match('/^\d{9}$/', $post['phone'])) {
            $this->errors[] = 'Phone must be a 9-digit number';
        }

        if (empty($post['choose'])) {
            $this->errors[] = 'Choose is required';
        }

        if ($post['choose'] === 1) {
            if (empty($post['account'])) {
                $this->errors[] = 'Account is required';
            } elseif (!preg_match('/^PL\d{26}$/', $post['account'])) {
                $this->errors[] = 'Account must be a valid Polish IBAN starting with "PL" followed by 26 digits';
            } elseif (!$this->validateIbanChecksum($post['account'])) {
                $this->errors[] = 'Invalid IBAN checksum';
            }
        }

        if (empty($post['client_no'])) {
            $this->errors[] = 'Client no is required';
        } elseif (!preg_match('/^000\d{3}-[A-Z]{5}$/', $post['client_no'])) {
            $this->errors[] = 'Client no must have the format 000DDD-WWWWW, where D is a digit and W is an uppercase letter';
        }

        if (empty($post['agreement1'])) {
            $this->errors[] = 'Agreement is required';
        }

        if (empty($post['agreement2'])) {
            $this->errors[] = 'Agreement is required';
        }

        if (count($this->errors) > 0) {
            throw new Exception();
        }
    }

    private function validateIbanChecksum($iban): bool
    {
        $iban = substr($iban, 4) . substr($iban, 0, 4);
        $iban = str_replace(
            range('A', 'Z'),
            range(10, 35),
            $iban
        );
        $checksum = intval(substr($iban, 0, 1));
        for ($i = 1; $i < strlen($iban); $i++) {
            $checksum = ($checksum * 10 + intval(substr($iban, $i, 1))) % 97;
        }
        return $checksum === 1;
    }

    private function restoreDb(): void
    {
        $dbDriver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sqlFile = 'zadanie1' . $dbDriver . '.sql';
        $this->db->restoreDb($sqlFile);
    }
}
