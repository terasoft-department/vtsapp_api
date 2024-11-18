 request.fields[''] = _selectedCustomerId ?? '';
      request.fields[''] = _imeiNumberController.text;
      request.fields[''] = _titleController.text;
      request.fields[''] = _carPlateNumberController.text;
      request.fields[''] = _contactPersonController.text;
      request.fields[''] = _mobileNumberController.text;
      request.fields[''] = _physicalLocationController.text;
      request.fields[''] = _problemReportedController.text;
      request.fields[''] =
          _selectedNatureOfProblem ?? '';
      request.fields[''] = _selectedServiceType ?? '';
      request.fields[''] = _dateAttendedController.text.isNotEmpty
          ? _dateAttendedController.text
          : '';
      request.fields[''] = _workDoneController.text;
      request.fields[''] = _clientCommentController.text;

      // Adding images if available
      if (_preWorkDoneImage != null) {
        request.files.add(await http.MultipartFile.fromPath(
          'pre_workdone_picture',
          _preWorkDoneImage!.path,
        ));
      }
      if (_postWorkDoneImage != null) {
        request.files.add(await http.MultipartFile.fromPath(
          '',
          _postWorkDoneImage!.path,
        ));
      }
      if (_carPlateNumberImage != null) {
        request.files.add(await http.MultipartFile.fromPath(
          '',
          _carPlateNumberImage!.path,
        ));
      }
      if (_tamperingEvidenceImage != null) {
        request.files.add(await http.MultipartFile.fromPath(
          '',
          _tamperingEvidenceImage!.path,
        ));
      }



