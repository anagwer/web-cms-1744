<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');

        $this->load->model('m_login');
        $this->load->model('m_data');

        /*
        * Cek session yang login,
        * jika session status tidak sama dengan 'telah_login', 
        * berarti pengguna belum login, maka halaman akan dialihkan ke halaman login.
        */
        if ($this->session->userdata('status') != "telah_login") {
            redirect('login?alert=belum_login');
        }
    }
    function tabel()
    {
      $data['title'] =  'DataTable';
      // memanggil view full.php
      $this->load->view('full',$data);
    }

    function index() {
        $data['jumlah_artikel'] = $this->m_data->get_data('artikel')->num_rows();
        //hitung jumlah kategori
        $data['jumlah_kategori'] = $this->m_data->get_data('kategori')->num_rows();
        //hitung jumlah pengguna
        $data['jumlah_pengguna'] = $this->m_data->get_data('pengguna')->num_rows();
        //hitung jumlah halaman
        $data['jumlah_halaman'] = $this->m_data->get_data('halaman')->num_rows();
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_index',$data);
        $this->load->view('dashboard/v_footer');
    }

    function keluar(){
        $this->session->sess_destroy();
        redirect('login?alert=logout');
    }

    // Fitur ganti password
    function ganti_password() {
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_ganti_password');
        $this->load->view('dashboard/v_footer');
    }

    function ganti_password_aksi() {
        // Set form validation
        $this->form_validation->set_rules('password_lama', 'Password Lama', 'required');
        $this->form_validation->set_rules('password_baru', 'Password Baru', 'required|min_length[8]');
        $this->form_validation->set_rules('konfirmasi_password', 'Konfirmasi Password', 'required|matches[password_baru]');

        if ($this->form_validation->run() !== false) {
            $password_lama       = $this->input->post('password_lama');
            $password_baru       = $this->input->post('password_baru');
            $konfirmasi_password = $this->input->post('konfirmasi_password');

            $where = array(
                'pengguna_id'       => $this->session->userdata('id'),
                'pengguna_password' => md5($password_lama)
            );

            $cek = $this->m_login->cek_login('pengguna', $where);

            if ($cek->num_rows() > 0) {
                $data = array(
                    'pengguna_password' => md5($password_baru)
                );
                $this->m_data->update_data('pengguna', $data, $where);
                redirect('dashboard/ganti_password?alert=sukses');
            } else {
                redirect('dashboard/ganti_password?alert=gagal');
            }
        } else {
            $this->load->view('dashboard/v_header');
            $this->load->view('dashboard/v_ganti_password');
            $this->load->view('dashboard/v_footer');
        }
    }

    // Fitur mengelola kategori
    function kategori() {
        $data['kategori'] = $this->m_data->get_data('kategori')->result();
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_kategori', $data);
        $this->load->view('dashboard/v_footer');
    }

    function kategori_tambah() {
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_kategori_tambah');
        $this->load->view('dashboard/v_footer');
    }

    function kategori_tambah_aksi() {
        $this->form_validation->set_rules('kategori', 'Kategori', 'required');

        if ($this->form_validation->run() != false) {
            $kategori = $this->input->post('kategori');
            $data = array(
                'kategori_nama' => $kategori,
                'kategori_slug' => strtolower(url_title($kategori))
            );
            $this->m_data->insert_data('kategori', $data);
            redirect(base_url().'dashboard/kategori');
        } else {
            $this->load->view('dashboard/v_header');
            $this->load->view('dashboard/v_kategori_tambah');
            $this->load->view('dashboard/v_footer');
        }
    }

    public function kategori_edit($id) {
        $where = array('kategori_id' => $id);
        $data['kategori'] = $this->m_data->edit_data('kategori', $where)->result();
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_kategori_edit', $data);
        $this->load->view('dashboard/v_footer');
    }

    public function kategori_update() {
        $this->form_validation->set_rules('kategori', 'Kategori', 'required');

        if ($this->form_validation->run() != false) {
            $id = $this->input->post('id');
            $kategori = $this->input->post('kategori');
            $where = array('kategori_id' => $id);
            $data = array(
                'kategori_nama' => $kategori,
                'kategori_slug' => strtolower(url_title($kategori))
            );
            $this->m_data->update_data('kategori', $data, $where);
            redirect(base_url().'dashboard/kategori');
        } else {
            $id = $this->input->post('id');
            $where = array('kategori_id' => $id);
            $data['kategori'] = $this->m_data->edit_data('kategori', $where)->result();
            $this->load->view('dashboard/v_header');
            $this->load->view('dashboard/v_kategori_edit', $data);
            $this->load->view('dashboard/v_footer');
        }
    }

    public function kategori_hapus($id) {
        $where = array('kategori_id' => $id);
        $this->m_data->delete_data('kategori', $where);
        redirect(base_url().'dashboard/kategori');
    }

    // Fitur mengelola artikel
    public function artikel() {
        $data['artikel'] = $this->db->query(
            'SELECT * FROM artikel, kategori, pengguna 
             WHERE artikel_kategori=kategori_id AND artikel_author=pengguna_id 
             ORDER BY artikel_id DESC'
        )->result();

        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_artikel', $data);
        $this->load->view('dashboard/v_footer');
    }

    public function artikel_tambah() {
        $data['kategori'] = $this->m_data->get_data('kategori')->result();
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_artikel_tambah', $data);
        $this->load->view('dashboard/v_footer');
    }

    public function artikel_aksi() {
        $this->form_validation->set_rules('judul', 'Judul', 'required|is_unique[artikel.artikel_judul]');
        $this->form_validation->set_rules('konten', 'Konten', 'required');
        $this->form_validation->set_rules('kategori', 'Kategori', 'required');

        if (empty($_FILES['sampul']['name'])) {
            $this->form_validation->set_rules('sampul', 'Gambar Sampul', 'required');
        }

        if ($this->form_validation->run() != false) {
            $config['upload_path']   = './gambar/artikel/';
            $config['allowed_types'] = 'gif|jpg|png';
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('sampul')) {
                // mengambil data tentang gambar
                $gambar = $this->upload->data();
                $tanggal = date('Y-m-d H:i:s');
                $judul   = $this->input->post('judul');
                $slug    = strtolower(url_title($judul));
                $konten  = $this->input->post('konten');
                $sampul  = $gambar['file_name'];
                $author  = $this->session->userdata('id');
                $kategori = $this->input->post('kategori');
                $status  = $this->input->post('status');

                $data = array(
                    'artikel_tanggal' => $tanggal,
                    'artikel_judul' => $judul,
                    'artikel_slug' => $slug,
                    'artikel_konten' => $konten,
                    'artikel_sampul' => $sampul,
                    'artikel_author' => $author,
                    'artikel_kategori' => $kategori,
                    'artikel_status' => $status
                );

                $this->m_data->insert_data('artikel', $data);
                redirect(base_url().'dashboard/artikel');
            } else {
                $this->form_validation->set_message('sampul', $this->upload->display_errors());
                $data['kategori'] = $this->m_data->get_data('kategori')->result();
                $this->load->view('dashboard/v_header');
                $this->load->view('dashboard/v_artikel_tambah', $data);
                $this->load->view('dashboard/v_footer');
            }
        } else {
            $data['kategori'] = $this->m_data->get_data('kategori')->result();
            $this->load->view('dashboard/v_header');
            $this->load->view('dashboard/v_artikel_tambah', $data);
            $this->load->view('dashboard/v_footer');
        }
    }

    public function artikel_edit($id) {
        $where = array('artikel_id' => $id);
        $data['artikel'] = $this->m_data->edit_data('artikel', $where)->result();
        $data['kategori'] = $this->m_data->get_data('kategori')->result();

        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_artikel_edit', $data);
        $this->load->view('dashboard/v_footer');
    }

    public function artikel_update() {
        $this->form_validation->set_rules('judul', 'Judul', 'required');
        $this->form_validation->set_rules('konten', 'Konten', 'required');
        $this->form_validation->set_rules('kategori', 'Kategori', 'required');

        if ($this->form_validation->run() != false) {
            $id = $this->input->post('id');
            $judul = $this->input->post('judul');
            $slug = strtolower(url_title($judul));
            $konten = $this->input->post('konten');
            $kategori = $this->input->post('kategori');
            $status = $this->input->post('status');

            $where = array('artikel_id' => $id);
            $data = array(
                'artikel_judul' => $judul,
                'artikel_slug' => $slug,
                'artikel_konten' => $konten,
                'artikel_kategori' => $kategori,
                'artikel_status' => $status
            );

            $this->m_data->update_data('artikel', $data, $where);

            if (!empty($_FILES['sampul']['name'])) {
                $config['upload_path'] = './gambar/artikel/';
                $config['allowed_types'] = 'gif|jpg|png';
                $this->load->library('upload', $config);

                if ($this->upload->do_upload('sampul')) {
                    $gambar = $this->upload->data();
                    $data = array(
                        'artikel_sampul' => $gambar['file_name']
                    );
                    $this->m_data->update_data('artikel', $data, $where);
                    redirect(base_url().'dashboard/artikel');
                } else {
                    $this->form_validation->set_message('sampul', $this->upload->display_errors());
                    $data['artikel'] = $this->m_data->edit_data('artikel', $where)->result();
                    $data['kategori'] = $this->m_data->get_data('kategori')->result();

                    $this->load->view('dashboard/v_header');
                    $this->load->view('dashboard/v_artikel_edit', $data);
                    $this->load->view('dashboard/v_footer');
                }
            } else {
                redirect(base_url().'dashboard/artikel');
            }
        } else {
            $id = $this->input->post('id');
            $where = array('artikel_id' => $id);
            $data['artikel'] = $this->m_data->edit_data('artikel', $where)->result();
            $data['kategori'] = $this->m_data->get_data('kategori')->result();

            $this->load->view('dashboard/v_header');
            $this->load->view('dashboard/v_artikel_edit', $data);
            $this->load->view('dashboard/v_footer');
        }
    }

    public function artikel_hapus($id) {
        $where = array('artikel_id' => $id);
        $this->m_data->delete_data('artikel', $where);
        redirect(base_url().'dashboard/artikel');
    }
    //fitur mengelola halaman
public function pages() {
    $data['halaman'] = $this->m_data->get_data('halaman')->result();
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pages',$data);
    $this->load->view('dashboard/v_footer');
}

public function pages_tambah() {
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pages_tambah');
    $this->load->view('dashboard/v_footer');
}

public function pages_aksi() {
    // Wajib isi judul dan konten
    $this->form_validation->set_rules('judul','Judul','required|is_unique[halaman.halaman_judul]');
    $this->form_validation->set_rules('konten','Konten','required');
    if ($this->form_validation->run() != false) {
    $judul = $this->input->post('judul');
    $slug = strtolower(url_title($judul));
    $konten = $this->input->post('konten');
    $data = array(
    'halaman_judul' => $judul,
    'halaman_slug' => $slug,
    'halaman_konten' => $konten
    );
    $this->m_data->insert_data('halaman',$data);
    redirect(base_url().'dashboard/pages');
    }else{
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pages_tambah');
    $this->load->view('dashboard/v_footer');
    }
    }

public function pages_edit($id) {
    $where = array('halaman_id' => $id );
    $data['halaman'] = $this->m_data->edit_data('halaman',$where)->result();
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pages_edit',$data);
    $this->load->view('dashboard/v_footer');
    }

function pages_update() {
    // Wajib isi judul dan konten
    $this->form_validation->set_rules('judul','Judul','required');
    $this->form_validation->set_rules('konten','Konten','required');
    if ($this->form_validation->run() != false) {
    $id = $this->input->post('id');
    $judul = $this->input->post('judul');
    $slug = strtolower(url_title($judul));
    $konten = $this->input->post('konten');
    $where = array(
    'halaman_id' => $id
    );
    $data = array(
    'halaman_judul' => $judul,
    'halaman_slug' => $slug,
    'halaman_konten' => $konten
    );
    $this->m_data->update_data('halaman', $data, $where);
    redirect(base_url().'dashboard/pages');
    }else{
    $id = $this->input->post('id');
    $where = array('halaman_id' => $id );
 $data['halaman'] = $this->m_data->edit_data('halaman',$where)->result();
 $this->load->view('dashboard/v_header');
 $this->load->view('dashboard/v_pages_edit',$data);
 $this->load->view('dashboard/v_footer');
 }
 }

 public function pages_hapus($id){
    $where = array(
    'halaman_id' => $id
    );
    $this->m_data->delete_data('halaman',$where);
    redirect(base_url().'dashboard/pages');
    }

//fitur profil pengguna
public function profil(){
    //ambil id pengguna yang sedang login
    $id_pengguna = $this->session->userdata('id');
 $where = array(
 'pengguna_id' => $id_pengguna
 );
 $data['pengguna'] = $this->m_data->edit_data('pengguna',$where)->result();
 $this->load->view('dashboard/v_header');
 $this->load->view('dashboard/v_profil',$data);
 $this->load->view('dashboard/v_footer');
 }

 public function profil_update() {
    //rules form wajib diisi untuk nama dan email
    $this->form_validation->set_rules('nama','Nama', 'Required');
    $this->form_validation->set_rules('email','Email', 'Required');
    if ($this->form_validation->run() != false) {
    $id = $this->session->userdata('id');
    $nama = $this->input->post('nama');
    $email = $this->input->post('email');
    $where = array(
    'pengguna_id' => $id
    );
    $data = array(
    'pengguna_nama' => $nama,
    'pengguna_email' => $email
    );
    $this->m_data->update_data('pengguna',$data,$where);
    redirect(base_url().'dashboard/profil/?alert=sukses');
    }else{
    //id pengguna yang sedang login
    $id_pengguna = $this->session->userdata('id');
    $where = array(
    'pengguna_id' => $id
    );
    $data['pengguna'] = $this->m_data->edit_data('pengguna',$where)->result();
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_profil',$data);
    $this->load->view('dashboard/v_footer');
    }
    }

//fitur untuk pengaturan website
public function pengaturan() {
    $data['pengaturan'] = $this->m_data->get_data('pengaturan')->result();
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pengaturan',$data);
    $this->load->view('dashboard/v_footer');
    }

public function pengaturan_update() {
    //Wjib diisi untuk dama dan deskripsi
    $this->form_validation->set_rules('nama', 'Nama', 'required');
    $this->form_validation->set_rules('deskripsi','Deskripsi', 'required');
    if ($this->form_validation->run() != false) {
    $nama = $this->input->post('nama');
    $deskripsi = $this->input->post('deskripsi');
    $link_facebook = $this->input->post('link_facebook');
    $link_twitter = $this->input->post('link_twitter');
    $link_instagram = $this->input->post('link_instagram');
    $link_github = $this->input->post('link_github');
    $where = array(
    );
    $data = array(
    'nama' => $nama,
    'deskripsi' => $deskripsi,
    'link_facebook' => $link_facebook,
    'link_twitter' => $link_twitter,
    'link_instagram' => $link_instagram,
    'link_github' => $link_github
    );
    //update pengaturan website
    $this->m_data->update_data('pengaturan',$data,$where);
    //periksa apakah ada gambar yang akan diupload
    if (!empty($_FILES['logo']['name'])) {
    $config['upload_path'] = './gambar/website/';
    $config['allowed_types'] = 'jpg|png';
    $this->load->library('upload',$config);
    if ($this->upload->do_upload('logo')) {
    //mengambil data logo yang akan diupload
    $gambar = $this->upload->data();
    $logo = $gambar['file_name'];
    $this->db->query("UPDATE pengaturan SET logo='$logo'");
    }
    }
    redirect(base_url().'dashboard/pengaturan/?alert=sukses');
    }else{
    $data['pengaturan'] = $this->m_data->get_data('pengaturan')->result();
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pengaturan',$data);
    $this->load->view('dashboard/v_footer');
    }
}

//fitur untuk mengelola pengguna
public function pengguna() {
    $data['pengguna'] = $this->m_data->get_data('pengguna')->result();
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pengguna',$data);
    $this->load->view('dashboard/v_footer');
    }

public function pengguna_tambah() {
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pengguna_tambah');
    $this->load->view('dashboard/v_footer');
    }

public function pengguna_tambah_aksi(){
    //rules untuk wajib diisi
    $this->form_validation->set_rules('nama', 'Nama Pengguna', 'required');
    $this->form_validation->set_rules('email', 'Email Pengguna', 'required');
    $this->form_validation->set_rules('username', 'Username Pengguna', 'required');
    $this->form_validation->set_rules('password', 'Password Pengguna', 'required|min_length[8]');

    $this->form_validation->set_rules('level', 'Level Pengguna', 'required');
    $this->form_validation->set_rules('status', 'Status Pengguna', 'required');
    if ($this->form_validation->run() != false) {
    $nama = $this->input->post('nama');
    $email = $this->input->post('email');
    $username = $this->input->post('username');
    $password = md5($this->input->post('password'));
    $level = $this->input->post('level');
    $status = $this->input->post('status');
    $data = array(
    'pengguna_nama' => $nama,
    'pengguna_email' => $email,
    'pengguna_username' => $username,
    'pengguna_password' => $password,
    'pengguna_level' => $level,
    'pengguna_status' => $status
    );
    $this->m_data->insert_data('pengguna',$data);
    redirect(base_url().'dashboard/pengguna');
    }else{
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pengguna_tambah');
    $this->load->view('dashboard/v_footer');
    }
 }

 public function pengguna_edit($id){
    $where = array(
    'pengguna_id' => $id
    );
    $data['pengguna'] = $this->m_data->edit_data('pengguna',$where)->result();
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pengguna_edit', $data);
    $this->load->view('dashboard/v_footer');
    }

public function pengguna_update(){
    //rules untuk wajib diisi
    $this->form_validation->set_rules('nama', 'Nama Pengguna', 'required');
    $this->form_validation->set_rules('email', 'Email Pengguna', 'required');
    $this->form_validation->set_rules('username', 'Username Pengguna', 'required');
    $this->form_validation->set_rules('level', 'Level Pengguna', 'required');
    $this->form_validation->set_rules('status', 'Status Pengguna', 'required');
    if ($this->form_validation->run() != false) {
    $id = $this->input->post('id');
    $nama = $this->input->post('nama');
    $email = $this->input->post('email');
    $username = $this->input->post('username');
    $password = md5($this->input->post('password'));
    $level = $this->input->post('level');
    $status = $this->input->post('status');

    //cek apabila password tidak diisi, maka kolom password tidakakan diupdate
    if ($this->input->post('password') == "") {
    $data = array(
    'pengguna_nama' => $nama,
    'pengguna_email' => $email,
    'pengguna_username' => $username,
    'pengguna_level' => $level,
    'pengguna_status' => $status
    );
    }else{
    $data = array(
    'pengguna_nama' => $nama,
    'pengguna_email' => $email,
    'pengguna_username' => $username,
    'pengguna_password' => $password,
    'pengguna_level' => $level,
    'pengguna_status' => $status
    );
    }
    $where = array(
    'pengguna_id' => $id
    );
    $this->m_data->update_data('pengguna',$data,$where);
    redirect(base_url().'dashboard/pengguna');
    }else{
    $id = $this->input->post('id');
    $where = array(
    'pengguna_id' => $id
    );
    $data['pengguna'] = $this->m_data->get_data('pengguna',$where)->result();
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pengguna_edit', $data);
    $this->load->view('dashboard/v_footer');
    }
}

public function pengguna_hapus($id) {
    $where = array(
    'pengguna_id' => $id
    );
    $data['pengguna_hapus'] = $this->m_data->edit_data('pengguna',$where)->row();
    $data['pengguna_lain'] = $this->db->query("SELECT * FROM pengguna WHERE pengguna_id != '$id'")->result();
    $this->load->view('dashboard/v_header');
    $this->load->view('dashboard/v_pengguna_hapus', $data);
    $this->load->view('dashboard/v_footer');
}

public function pengguna_hapus_aksi(){
    $pengguna_hapus = $this->input->post('pengguna_hapus');
    $pengguna_tujuan = $this->input->post('pengguna_tujuan');
    //hapus data pengguna
    $where = array(
    'pengguna_id' => $pengguna_hapus
    );
    $this->m_data->delete_data('pengguna',$where);
    //pindahkan semua artikel pengguna yang dihapus pada pengguna tujuan
    $w = array(
    'artikel_author' => $pengguna_hapus
    );
    $d = array(
    'artikel_author' => $pengguna_tujuan
    );
    $this->m_data->update_data('artikel',$d,$w);
    redirect(base_url().'dashboard/pengguna');
    }

    // =================================================================
    // START - CRUD LAYANAN
    // =================================================================

    public function layanan() {
        $data['layanan'] = $this->m_data->get_data('layanan')->result();
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_layanan', $data);
        $this->load->view('dashboard/v_footer');
    }

    public function layanan_tambah() {
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_layanan_tambah');
        $this->load->view('dashboard/v_footer');
    }

    public function layanan_aksi() {
        $this->form_validation->set_rules('nama', 'Nama Layanan', 'required|is_unique[layanan.nama]');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required');

        if ($this->form_validation->run() != FALSE) {
            $nama = $this->input->post('nama');
            $deskripsi = $this->input->post('deskripsi');
            $tanggal = date('Y-m-d H:i:s');

            // Upload gambar
            $config['upload_path'] = './gambar/layanan/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('gambar')) {
                $gambar = $this->upload->data('file_name');
            } else {
                $gambar = '';
            }

            $data = array(
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'tanggal' => $tanggal,
                'gambar' => $gambar
            );

            $this->m_data->insert_data('layanan', $data);
            redirect(base_url('dashboard/layanan'));
        } else {
            $this->load->view('dashboard/v_header');
            $this->load->view('dashboard/v_layanan_tambah');
            $this->load->view('dashboard/v_footer');
        }
    }

    public function layanan_edit($id) {
        $where = array('id' => $id);
        $data['layanan'] = $this->m_data->edit_data('layanan', $where)->result();

        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_layanan_edit', $data);
        $this->load->view('dashboard/v_footer');
    }

    public function layanan_update() {
        $this->form_validation->set_rules('nama', 'Nama Layanan', 'required');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required');

        if ($this->form_validation->run() != FALSE) {
            $id = $this->input->post('id');
            $nama = $this->input->post('nama');
            $deskripsi = $this->input->post('deskripsi');
            $tanggal = date('Y-m-d H:i:s');

            // Upload gambar baru jika diunggah
            $config['upload_path'] = './gambar/layanan/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $this->load->library('upload', $config);

            $where = array('id' => $id);
            $data_lama = $this->m_data->edit_data('layanan', $where)->row();
            $gambar_lama = $data_lama->gambar;

            if ($this->upload->do_upload('gambar')) {
                // Hapus gambar lama jika ada
                if ($gambar_lama && file_exists('./gambar/layanan/' . $gambar_lama)) {
                    unlink('./gambar/layanan/' . $gambar_lama);
                }
                $gambar_baru = $this->upload->data('file_name');
            } else {
                $gambar_baru = $gambar_lama;
            }

            $data = array(
                'nama' => $nama,
                'tanggal' => $tanggal,
                'deskripsi' => $deskripsi,
                'gambar' => $gambar_baru
            );

            $this->m_data->update_data('layanan', $data, $where);
            redirect(base_url('dashboard/layanan'));
        } else {
            $id = $this->input->post('id');
            $where = array('id' => $id);
            $data['layanan'] = $this->m_data->edit_data('layanan', $where)->result();
            $this->load->view('dashboard/v_header');
            $this->load->view('dashboard/v_layanan_edit', $data);
            $this->load->view('dashboard/v_footer');
        }
    }

    public function layanan_hapus($id) {
        $where = array('id' => $id);
        $layanan = $this->m_data->edit_data('layanan', $where)->row();

        // Hapus file gambar jika ada
        if (!empty($layanan->gambar) && file_exists('./gambar/layanan/' . $layanan->gambar)) {
            unlink('./gambar/layanan/' . $layanan->gambar);
        }

        $this->m_data->delete_data('layanan', $where);
        redirect(base_url('dashboard/layanan'));
    }

	public function portofolio() {
        $data['portofolio'] = $this->db->query(
            'SELECT * FROM portofolio, kategori
             WHERE id_kategori=kategori_id 
             ORDER BY id DESC'
        )->result();
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_portofolio', $data);
        $this->load->view('dashboard/v_footer');
    }

    public function portofolio_tambah() {
		
        $data['kategori'] = $this->m_data->get_data('kategori')->result();
        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_portofolio_tambah',$data);
        $this->load->view('dashboard/v_footer');
    }

    public function portofolio_aksi() {
        $this->form_validation->set_rules('judul', 'Judul Portofolio', 'required|is_unique[portofolio.judul]');
        $this->form_validation->set_rules('kategori', 'Kategori', 'required');

        if ($this->form_validation->run() != FALSE) {
            $judul = $this->input->post('judul');
            $tanggal = date('Y/m/d H:i:s');
            $kategori = $this->input->post('kategori');
            $slug = strtolower(url_title($judul));

            // Upload gambar
            $config['upload_path'] = './gambar/portofolio/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('gambar')) {
                $gambar = $this->upload->data('file_name');
            } else {
                $gambar = '';
            }

            $data = array(
                'judul' => $judul,
                'tanggal' => $tanggal,
                'id_kategori' => $kategori,
                'slug' => $slug,
                'gambar' => $gambar
            );

            $this->m_data->insert_data('portofolio', $data);
            redirect(base_url('dashboard/portofolio'));
        } else {
            $this->load->view('dashboard/v_header');
            $this->load->view('dashboard/v_portofolio_tambah');
            $this->load->view('dashboard/v_footer');
        }
    }

    public function portofolio_edit($id) {
        $where = array('id' => $id);
        $data['kategori'] = $this->m_data->get_data('kategori')->result();
        $data['portofolio'] = $this->m_data->edit_data('portofolio', $where)->result();

        $this->load->view('dashboard/v_header');
        $this->load->view('dashboard/v_portofolio_edit', $data);
        $this->load->view('dashboard/v_footer');
    }

    public function portofolio_update() {
        $this->form_validation->set_rules('judul', 'Judul Portofolio', 'required');
        $this->form_validation->set_rules('kategori', 'Kategori', 'required');

        if ($this->form_validation->run() != FALSE) {
            $id = $this->input->post('id');
            $judul = $this->input->post('judul');
          	$tanggal = date('Y-m-d H:i:s');
            $kategori = $this->input->post('kategori');
            $slug = strtolower(url_title($judul));

            // Upload gambar baru jika diunggah
            $config['upload_path'] = './gambar/portofolio/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $this->load->library('upload', $config);

            $where = array('id' => $id);
            $data_lama = $this->m_data->edit_data('portofolio', $where)->row();
            $gambar_lama = $data_lama->gambar;

            if ($this->upload->do_upload('gambar')) {
                // Hapus gambar lama jika ada
                if ($gambar_lama && file_exists('./gambar/portofolio/' . $gambar_lama)) {
                    unlink('./gambar/portofolio/' . $gambar_lama);
                }
                $gambar_baru = $this->upload->data('file_name');
            } else {
                $gambar_baru = $gambar_lama;
            }

            $data = array(
                'judul' => $judul,
                'tanggal' => $tanggal,
                'id_kategori' => $kategori,
                'slug' => $slug,
                'gambar' => $gambar_baru
            );

            $this->m_data->update_data('portofolio', $data, $where);
            redirect(base_url('dashboard/portofolio'));
        } else {
            $id = $this->input->post('id');
            $where = array('id' => $id);
            $data['portofolio'] = $this->m_data->edit_data('portofolio', $where)->result();
            $this->load->view('dashboard/v_header');
            $this->load->view('dashboard/v_portofolio_edit', $data);
            $this->load->view('dashboard/v_footer');
        }
    }

    public function portofolio_hapus($id) {
        $where = array('id' => $id);
        $portofolio = $this->m_data->edit_data('portofolio', $where)->row();

        // Hapus file gambar jika ada
        if (!empty($portofolio->gambar) && file_exists('./gambar/portofolio/' . $portofolio->gambar)) {
            unlink('./gambar/portofolio/' . $portofolio->gambar);
        }

        $this->m_data->delete_data('portofolio', $where);
        redirect(base_url('dashboard/portofolio'));
    }

}

