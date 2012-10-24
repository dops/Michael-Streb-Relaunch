function setFormValue(frmName, fldName, value, submitFrm) {
    var frm = document.forms[frmName];
    var fld = frm.elements[fldName];
    
    if (fldName == 'order') { if (fld.value == value) { frm.elements['orderdir'].value = (frm.elements['orderdir'].value == 'ASC' ? 'DESC' : 'ASC'); } }
    fld.value = value;
    
    if (submitFrm == true) { frm.submit(); return true; }
    return false;
}