<?php

class user extends CI_Model
{
	public function addUser($fullname, $email, $password)
	{
		$data = array('fullname' => $fullname, 'email' => $email, 'password' => $password);
		if ($this->db->insert('users', $data)) 
			return true;
		return false;
	}

	public function getUsers()
	{
		return $this->db->select('*')->from('users')->get()->result();
	}

	public function getUserdataById($id)
	{
		return $this->db->select('*')->from('users')->where('id', $id)->get()->row();
	}

	public function getUserdataByEmail($email)
	{
		return $this->db->select('*')->from('users')->where('email', $email)->get()->row();
	}
   
	public function validate_email($email)
	{
		return $this->db->select('*')->from('users')->where('email', $email)->count_all_results();
	}

	// public function getNonAdminUsersCount()
	// {
	// 	return $this->db->select('*')->from('users')->where('role !=', 1)->count_all_results();
	// }
	// public function getNonAdminUsers($limit, $start)
	// {
	// 	return $this->db->select('*')->from('users')->where('role !=', 1)->limit($limit, $start)->get()->result();
	// }
	
	public function getAllUsersCount()
	{
		return $this->db->select('*')->from('users')->count_all_results();
	}
	public function getAllUsers($limit, $start)
	{
		return $this->db->select('*')->from('users')->limit($limit, $start)->get()->result();
	}

	public function updateNamePassword($id, $fullname, $password)
	{
		$this->db->where('id', $id);
		return $this->db->update('users', array('fullname' => $fullname, 'password' => $password));
	}

	public function updateName($id, $fullname)
	{
		$this->db->where('id', $id);
		return $this->db->update('users', array('fullname' => $fullname));
	}

	public function deleteUser($id){
		return $this->db->where('id', $id)->delete('users');
	}

	public function setRecoveryString($email, $recstr)
	{
		return $this->db->update('users', array('recoverystring' => $recstr), array('email' => $email));
	}
	
	public function setStatus($id, $status)
	{
		return $this->db->update('users', array('status' => $status), array('id' => $id));
	}

	public function setUserRole($userId, $role)
	{
		return $this->db->update('users', array('role' => $role), array('id' => $userId));
	}

	public function hasRecoveryString($userid, $recstr)
	{
		return $this->db->select('*')->from('users')->where('id', $userid)->where('recoverystring', $recstr)->count_all_results();
	}

	public function resetPassword($userid, $recstr, $hpwd)
	{
		return $this->db->update('users', array('recoverystring' => '', 'password' => $hpwd), array('id' => $userid, 'recoverystring' => $recstr));
	}

}
